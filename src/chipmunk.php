<?php

namespace IDCT\Db\Chipmunk;

/**
* Chipmunk Database Connector
*
* @package chipmunk
* @version 0.1.0
*
* @copyright Bartosz Pachołek
* @copyleft Bartosz Pachołek
* @author Bartosz Pachołek
* @license http://opensource.org/licenses/MIT (The MIT License)
*
* Copyright (c) 2014, IDCT IdeaConnect Bartosz Pachołek (http://www.idct.pl/)
* Copyleft (c) 2014, IDCT IdeaConnect Bartosz Pachołek (http://www.idct.pl/)
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
* INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
* PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
* OR OTHER DEALINGS IN THE SOFTWARE.
*/

class chipmunk {

    protected $buf = 1024;

    protected $host;
    protected $port;

    /**
     * Initializes a new instance of the chipmunk db connector
     * @param string $host
     * @param int $port
     * @return self
     */
    public function __construct($host = '127.0.0.1', $port = 8909) {

        $this->setHost($host)
             ->setPort($port);

        return $this;
    }

    /**
     * Gets the host to which the connector connects to reach the db.
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Gets the port on which the connector connects to the db
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Sets the host to which the connector should connect to reach the database.
     * Defaults to localhost.
     * @param string $host
     * @return self
     */
    public function setHost($host) {
        if(is_string($host) && !empty($host)) {
            $this->host = $host;
        } else {
            trigger_error("Invalid host provided, setting localhost", E_USER_WARNING);
        }
        return $this;
    }

    /**
     * Sets the port to connect on to the database.
     * Defaults to 8909.
     * @param int $port
     * @return self
     */
    public function setPort($port) {
        if(is_int($port)) {
            $this->port = $port;
        } else {
            trigger_error("Invalid port provided, setting 8909", E_USER_WARNING);
        }
        return $this;
    }

    /**
     * Method which communicates with the database to send the data. Returns the opened socket.
     * @param string $msg Message (data) to be saved
     * @return resource
     */
    protected function sendRequest($msg)
    {
        $buf = $this->buf;
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_connect($socket, $this->getHost(),$this->getPort());
        $maxLength = strlen($msg);
        $position = 0;
        $len = $buf;

        while($maxLength > 0) {
            $len = ($buf > $maxLength) ? $maxLength : $buf;
            $query = substr($msg, $position, $len);
            $s = socket_send($socket, $query, $len, 0);
            $maxLength -= $len;
            $position += $len;
        }

        return $socket;
    }

    /**
     * Method which transforms an array of metadatas to a format acceptable by the database.
     * @param array $metadata
     * @return string
     */
    protected function parseMetadata($metadata)
    {
        $first = true;
        $metadataParsed = "";
        foreach($metadata as $key => $value) {
                if($first === true) {
                        $first = false;
                } else {
                        $metadataParsed .= "#";
                }

                if($value instanceof searchParameter) {
                    $metadataParsed .= (string)$value;
                } else {
                    $metadataParsed .= $key . ":" . $value;
                }

        }

        return $metadataParsed;
    }

    /**
     * Method which communicates with the database to get the response for last operation
     * @param resource $socket
     * @return string
     */
    protected function getResponse($socket)
    {
        $buf = $this->buf;
        $response = "";
    	while (true) {
            $mesg = "";
            $n = @socket_recv($socket, $mesg, $buf, MSG_PEEK);
		    if ($n < 1)
			    break;
		    $n = @socket_recv($socket, $mesg, $n, MSG_WAITALL);
		    $response .= $mesg;
		    if ($n < 1)
			    break;
	    }

        socket_close($socket);

        return $response;
    }

    /**
    * Gets the data from under the given identifier
    *
    * @param string identifier
    * @return mixed|NULL response. NULL on failure or no data
    */
	public function get($identifier)
	{
		$msg = "G" . $identifier;
		$socket = $this->sendRequest($msg);
        $response = $this->getResponse($socket);
		return ($response !== "NODATA") ? unserialize($response) : null;
	}

    /**
     * Removes the data from under the given identifier
     *
     * @param string identifier
     * @return mixed|NULL response. NULL on failure or no data
     */
    public function remove($identifier)
	{
		$msg = "R" . $identifier;
		$socket = $this->sendRequest($msg);
        $response = $this->getResponse($socket);
		return ($response !== "NODATA") ? json_decode($response) : null;
	}

    /**
    * Performs an OR search for the given metadata parameters within the given subset of identifiers (or NULL for search within all db entries)
    *
    * @param array metadata
    * @param array|NULL subset of identifiers or NULL to search within all
    * @return array array of matched identifiers
    */
    public function findOr($metadata, $subset = null)
    {
        $metadataParsed = $this->parseMetadata($metadata);
        $msg = "O" . $metadataParsed;
		$msg .= "\n";
		if($subset !== null)
		{
			$msg .= implode('#',$subset);
		}
		$socket = $this->sendRequest($msg);
        $response = $this->getResponse($socket);
        return ($response !== "NODATA") ? explode('|',$response) : null;
    }

    /**
    * Performs an AND search for the given metadata parameters within the given subset of identifiers (or NULL for search within all db entries)
    *
    * @param array metadata
    * @param array|NULL subset of identifiers or NULL to search within all
    * @return array array of matched identifiers
    */
    public function findAnd($metadata, $subset= null)
    {
        $metadataParsed = $this->parseMetadata($metadata);

        $msg = "A" . $metadataParsed;
		$msg .= "\n";
		if($subset !== null)
		{
			$msg .= implode('#',$subset);
		}
        $socket = $this->sendRequest($msg);
        $response = $this->getResponse($socket);
        return ($response !== "NODATA") ? explode('|',$response) : null;
    }

    /**
    * Sets the data under the given identifier with set metadata
    * TODO: host and port modification
    *
    * @param string identifier
    * @param array array with metadata
    * @param mixed data to be saved (must be serializable)
    */
	public function set($identifier, $metadata, $data)
	{
		$metadataParsed = $this->parseMetadata($metadata);

		$msg = "S" . $identifier . "|" . $metadataParsed . "|" . serialize($data);
		$socket = $this->sendRequest($msg);
		socket_recvfrom($socket, $msg, $this->buf, 0, $from = '', $port = 0);
        socket_close($socket);
		return ($msg === "SAVED") ? true : false;
	}
}
