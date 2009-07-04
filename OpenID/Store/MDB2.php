<?php
/**
 * OpenID_Store_MDB2
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Store_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

require_once 'MDB2.php';
require_once 'OpenID/Store/Interface.php';
require_once 'OpenID/Store/Exception.php';
require_once 'OpenID.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Association.php';
require_once 'OpenID/Nonce.php';

/**
 * A first pass at SQL support via MDB2.  This may have some MySQL specific things
 * so it might get refactored a bit to support other DBs.
 * 
 * @uses      OpenID_Store_Interface
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Store_MDB2 implements OpenID_Store_Interface
{
    /**
     * Instance of MDB2
     * 
     * @var mixed
     */
    protected $db = null;

    /**
     * Table names which you can override in a child class
     * 
     * @var array
     */
    protected $tableNames = array(
        'nonce'       => 'OpenIDNonces',
        'association' => 'OpenIDAssociations',
        'discovery'   => 'OpenIDDiscovery',
    );

    /**
     * Calls MDB2::factory().  Connections are lazy loaded upon queries.
     * 
     * @param array $options Array of options to pass to MDB2::factory().  Note that
     *                       you must also include the key 'dsn', which is used as
     *                       the first argument to MDB2::factory(), and is not 
     *                       passed with the options argument.
     * 
     * @throws OpenID_Store_Exception on error or missing DSN in options
     * @return void
     */
    public function __construct(array $options)
    {
        if (!isset($options['dsn'])) {
            throw new OpenID_Store_Exception('Missing dsn from options');
        }

        $dsn = $options['dsn'];
        unset($options['dsn']);
        $this->db = MDB2::factory($dsn, $options);

        if (PEAR::isError($this->db)) {
            throw new OpenID_Store_Exception('Error connecting to DB', $this->db);
        }
    }

    /**
     * Creates tables
     * 
     * @throws OpenID_Store_Exception on failure to create tables
     * @return OpenID_Store_MDB2
     */
    public function createTables()
    {
        $nonceCreate = "CREATE TABLE {$this->tableNames['nonce']} (
                          uri VARCHAR(2047) NOT NULL,
                          nonce VARCHAR(100) NOT NULL,
                          created INTEGER NOT NULL,
                          UNIQUE (uri(255), nonce, created)
                        ) ENGINE=InnoDB";

        $assocCreate = "CREATE TABLE {$this->tableNames['association']} (
                          uri VARCHAR(2047) NOT NULL,
                          assocHandle VARCHAR(255) NOT NULL,
                          sharedSecret BLOB NOT NULL,
                          created INTEGER NOT NULL,
                          expiresIn INTEGER NOT NULL,
                          assocType VARCHAR(64) NOT NULL,
                          PRIMARY KEY (uri(255), assocHandle)
                        ) ENGINE=InnoDB";

        $discoveryCreate = "CREATE TABLE {$this->tableNames['discovery']} (
                              identifier VARCHAR(2047) NOT NULL,
                              serialized_discover BLOB NOT NULL,
                              expires INTEGER NOT NULL,
                              PRIMARY KEY (identifier(255))
                            ) ENGINE=InnoDB";

        $queries = array($nonceCreate, $assocCreate, $discoveryCreate);

        foreach ($queries as $sql) {
            $result = $this->db->exec($sql);
            if (PEAR::isError($result)) {
                throw new OpenID_Store_Exception(
                    'Error creating table', $result);
            }
        }

        return $this;
    }

    /**
     * A shortcut to handle the error checking of prepare()/execute() in one place.
     * 
     * @param string $sql  The SQL to prepare
     * @param array  $args The corresponding arguments
     * 
     * @throws OpenID_Store_Exception on error
     * @return MDB2_Result
     */
    protected function prepareExecute($sql, array $args)
    {
        $prepared = $this->db->prepare($sql);
        if (PEAR::isError($prepared)) {
            throw new OpenID_Store_Exception('Error preparing statement', $prepared);
        }

        $result = $prepared->execute($args);
        $prepared->free();
        if (PEAR::isError($result)) {
            throw new OpenID_Store_Exception(
                'Error executing prepared statement', $result
            );
        }
        return $result;
    }

    /**
     * Gets an instance of OpenID_Discover from the SQL server if it exists.
     * 
     * @param string $identifier The user supplied identifier
     * 
     * @return false on failure, OpenID_Discover on success
     */
    public function getDiscover($identifier)
    {
        $normalized = OpenID::normalizeIdentifier($identifier);

        $sql = "SELECT serialized_discover
                    FROM {$this->tableNames['discovery']}
                    WHERE identifier = ?
                    AND expires > ?";

        $result = $this->prepareExecute($sql, array($normalized, time()));
        if (!$result->numRows()) {
            return false;
        }

        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $result->free();

        return unserialize($row['serialized_discover']);
    }

    /**
     * Adds discoverd infomation to the SQL server
     * 
     * @param OpenID_Discover $discover The OpenID_Discover instance
     * @param int             $expire   The time (in seconds) that the cached object
     *                                  should live
     * 
     * @return OpenID_Store_MDB2
     */
    public function setDiscover(OpenID_Discover $discover, $expire = 3600)
    {
        $sql = "REPLACE INTO {$this->tableNames['discovery']} 
                (identifier, serialized_discover, expires)
                VALUES (?, ?, ?)";

        $this->prepareExecute($sql, array($discover->identifier,
                                          serialize($discover),
                                          time() + $expire));
        return $this;
    }

    /**
     * Gets an association from the SQL server
     * 
     * @param string $uri The OP Endpoint URL
     * 
     * @return OpenID_Association on success, false on failure
     */
    public function getAssociation($uri)
    {
        $sql = "SELECT *
                    FROM {$this->tableNames['association']}
                    WHERE uri = ?";

        $result = $this->prepareExecute($sql, array($uri));
        if (!$result->numRows()) {
            return false;
        }

        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $result->free();

        if (($row['expiresin'] + $row['created']) < time()) {
            return false;
        }

        $association = new OpenID_Association(array(
            'uri'          => $row['uri'],
            'expiresIn'    => $row['expiresin'],
            'created'      => $row['created'],
            'assocType'    => $row['assoctype'],
            'assocHandle'  => $row['assochandle'],
            'sharedSecret' => $row['sharedsecret']
        ));

        return $association;
    }

    /**
     * Sets an association in the SQL server
     * 
     * @param OpenID_Association $association An instance of OpenID_Association
     * 
     * @return OpenID_Store_MDB2
     */
    public function setAssociation(OpenID_Association $association)
    {
        $sql = "REPLACE INTO {$this->tableNames['association']}
                    (uri, assocHandle, sharedSecret, created, expiresIn, assocType)
                    VALUES (?, ?, ?, ?, ?, ?)";

        $args = array(
            $association->uri,
            $association->assocHandle,
            $association->sharedSecret,
            $association->created,
            $association->expiresIn,
            $association->assocType
        );
        $this->prepareExecute($sql, $args);

        return $this;
    }

    /**
     * Deletes an association from the SQL server
     * 
     * @param string $uri The OP Endpoint URL
     * 
     * @return OpenID_Store_MDB2
     */
    public function deleteAssociation($uri)
    {
        $sql = "DELETE FROM {$this->tableNames['association']}
                    WHERE uri = ?";

        $result = $this->prepareExecute($sql, array($uri));

        return $this;
    }

    /**
     * Gets a nonce from the SQL server if it exists
     * 
     * @param string $nonce The nonce to retrieve
     * @param string $opURL The OP Endpoint URL that it is associated with
     * 
     * @return string (nonce) on success, false on failure
     */
    public function getNonce($nonce, $opURL)
    {
        $sql = "SELECT nonce FROM {$this->tableNames['nonce']}
                    WHERE uri = ?
                    AND nonce = ?";

        $result = $this->prepareExecute($sql, array($opURL, $nonce));
        if (!$result->numRows()) {
            return false;
        }

        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $result->free();

        return $row['nonce'];
    }

    /**
     * Sets a nonce in the SQL server
     * 
     * @param string $nonce The nonce value to set
     * @param string $opURL The OP Endpoint URL it is associated with
     * 
     * @return OpenID_Store_MDB2
     */
    public function setNonce($nonce, $opURL)
    {
        $sql = "INSERT INTO {$this->tableNames['nonce']}
                    (uri, nonce, created)
                    VALUES (?, ?, ?)";
        $this->prepareExecute($sql, array($opURL, $nonce, time()));

        return $this;
    }

    /**
     * Deletes a nonce from the SQL server
     * 
     * @param string $nonce The nonce value
     * @param string $opURL The OP Endpoint URL it is associated with
     * 
     * @return OpenID_Store_MDB2
     */
    public function deleteNonce($nonce, $opURL)
    {
        $sql = "DELETE FROM {$this->tableNames['nonce']}
                    WHERE uri = ?
                    AND nonce = ?";

        $result = $this->prepareExecute($sql, array($opURL, $nonce));

        return $this;
    }
}
?>
