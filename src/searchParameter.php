<?php

namespace IDCT\Db\Chipmunk;

/**
 * Chipmunk Metadata Search Parameter
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

class SearchParameter {

    /**
     * value expected (or not: depending on the condition) in the metadata for the given key.
     * WARNING: line breaks, pipes, hash symbols are forbidded
     *
     * @var string
     */
	protected $value;

    /**
     * condition: EQUALS (=) or NOT (!) for the comaprison in the metadata search
     *
     * @var string
     */
	protected $condition;

    /**
     * key (identifier) of the metadata entry to compare
     * WARNING: line breaks, pipes, hash symbols are forbidded
     *
     * @var string
     */
	protected $key;

    /**
     * Constructor of the search parameter.
     *
     * @param string Key (identifier) of the metadata entry to compare. WARNING: line breaks, pipes, hash symbols are forbidden.
     * @param string Condition (symbol) EQUALS (=) or NOT (!) for the comaprison in the metadata search
     * @param string Value expected (or not: depending on the condition) in the metadata for the given key. WARNING: line breaks, pipes, hash symbols are forbidden.
     */
	function __construct($key, $condition, $value) {
		$this->condition = $condition;
		$this->value = $value;
		$this->key = $key;
		return $this;
	}

    /**
     * Sets the key, identifier for the metadata search
     *
     * @param string Key (identifier) of the metadata entry to compare. WARNING: line breaks, pipes, hash symbols are forbidden.
     */
    public function setKey($key) {
        $this->key = $key;

        return $this;
    }

    /**
     * Sets the condition for the comparison
     *
     * @param string Condition (symbol) EQUALS (=) or NOT (!) for the comaprison in the metadata search
     */
    public function setCondition($condition) {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Sets the value for the comparison
     *
     * @param string Value expected (or not: depending on the condition) in the metadata for the given key. WARNING: line breaks, pipes, hash symbols are forbidden.
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the search parameter in a format ready for use within the connector
     *
     * @return string
     */
	public function __toString() {
		return $this->key . ":" . $this->condition . ":" . $this->value;
	}
}
