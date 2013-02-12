<?php
/**
 * provides an interface to execute various operations on the remote server
 * using the pure-php library phpseclib
 * @package  Base
 * @author  Patrick Heck <patrick@patrickheck.de>
 */

class SSHSecLib extends SSH {
 	
 	 /**
     * the constructor
     *
     * at the moment defaults to not being connected
     */
    public function __construct() {
        $this->connected = false;
    }
 	
    /**
     * connect to the remote server
     */
    public function connect() {
        if (!$this->connected) {
            debug('connecting');
            $this->connection = new Net_SSH2(REMOTE_HOST . ":" . REMOTE_PORT);
            if (REMOTE_USE_KEY) {
                $key = new Crypt_RSA();
                $key->loadKey(file_get_contents(REMOTE_PRIV_KEY_PATH));
                if (!$this->connection->login(REMOTE_USER, $key)) {
                    exit('Login Failed');
                }
            }
            $this->connected = true;
        }
    }

    public function runCommand($cmd) {
      $this->getConnection();
      if (($output = $this->connection->exec($cmd)) === false) {
        throw new Exception('SSH command failed');
      }
      return $output;
    }

    public function scp($remote_path, $local_path) {
        $sftp = new Net_SFTP(REMOTE_HOST . ":" . REMOTE_PORT);
        $key = new Crypt_RSA();
        $key->loadKey(file_get_contents(REMOTE_PRIV_KEY_PATH));
        if (!$sftp->login(REMOTE_USER, $key)) {
           exit('Login Failed');
        }

        if ($sftp->get($remote_path, $local_path)) {
            return true;
        } else {
            die('could not copy file from remote server');
        }
    }

}