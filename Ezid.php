<?php
/**
 * User: jcdalton@wm.edu
 * Date: 3/4/16
 * Time: 4:25 PM
 */

namespace banetbi\ezid;
use \InvalidArgumentException;
use \LogicException;


/**
 * Class Ezid
 * @package banetbi\ezid
 *
 * Wrapper class to assist in EZID DOI and ArcID management
 */
class Ezid
{

    protected $strUserId;
    protected $strPassword;
    protected $strDoiShoulder;
    protected $strArcShoulder;

    /**
     * Identifier type matters for operation
     */
    const IDENTIFIER_TYPE_DOI = 1;
    const IDENTIFIER_TYPE_ARC = 2;

    /**
     * Ezid constructor.
     * @param string $strUserId
     * @param string $strPassword
     * @param string $strDoiShoulder
     * @param string $strArcShoulder
     * @throws InvalidArgumentException
     */
    public function __construct($strUserId, $strPassword, $strDoiShoulder, $strArcShoulder)
    {
        if(is_string($strUserId) && $strUserId !== '') {
            if(is_string($strPassword) && $strPassword !== '') {
                if(is_string($strDoiShoulder) && $strDoiShoulder !== '') {
                    if(is_string($strArcShoulder) && $strArcShoulder !== '') {
                        $this->strUserId = $strUserId;
                        $this->strPassword = $strPassword;
                        $this->strDoiShoulder = $strDoiShoulder;
                        $this->strArcShoulder = $strArcShoulder;

                    }
                    else {
                        throw new \InvalidArgumentException("Invalid ArcShoulder $strArcShoulder submitted.");
                    }
                }
                else {
                    throw new \InvalidArgumentException("Invalid DoiShoulder $strDoiShoulder submitted.");
                }
            }
            else {
                throw new \InvalidArgumentException("Invalid Password $strPassword submitted.");
            }
        }
        else {
            throw new \InvalidArgumentException("Invalid UserId $strUserId submitted.");
        }
    }

    /**
     * @param $strIdentifier
     * @return string
     * @throws InvalidArgumentException
     * @throws LogicException
     * Either an ArcID or DOI to query
     */
    public static function getIdInformation($strIdentifier) {
        if(!is_string($strIdentifier) || $strIdentifier == '' ){
            throw new \InvalidArgumentException("Invalid Identifier $strIdentifier submitted.");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://ezid.cdlib.org/id/' . $strIdentifier);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
            if(strpos($line, 'success:') !== false) {
                return $output;
            }
            elseif(strpos($line, 'error:') !== false) {
                throw new \LogicException($line);
            }
            break;
        }
    }

    /**
     * @param string $strShoulder
     * @param int $intIdentifierType
     * @param array $arrElements
     * @throws InvalidArgumentException
     * @return string
     *
     * Mints a new identifier at the $strShoulder supplied. $intIdentifier specifies which type of
     * identifier you want to generate which will force required checks to be made for the specified
     * type. Each element in $arrElements will be submitted as individual metadata elements.
     */
    public function mintIdentifier($strShoulder, $intIdentifierType, array $arrElements) {
        if(is_string($strShoulder) && $strShoulder != '') {
            if(is_int($intIdentifierType) && $intIdentifierType >= 1 && $intIdentifierType <= 2) {
                if(count($arrElements) > 0) {
                    $input = '';
                    foreach($arrElements as $key => $value) {
                        $input .= "$key: " .  ucfirst($value) . "\n";
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/shoulder/'.$strShoulder);
                    curl_setopt($ch, CURLOPT_USERPWD, "$this->strUserId:$this->strPassword");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array('Content-Type: text/plain; charset=UTF-8',
                            'Content-Length: ' . strlen($input)));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);
                    foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
                        if (strpos($line, 'success:') !== false) {
                            return $output;
                        } elseif (strpos($line, 'error:') !== false) {
                            throw new \LogicException($line);
                        }
                        break;
                    }

                }
                else {
                    throw new \InvalidArgumentException("arrElements cannot be empty.");
                }
            }
            else {
                throw new \InvalidArgumentException("Value $intIdentifierType not valid for intIdentifierType.");
            }
        }
        else {
            throw new \InvalidArgumentException("Shoulder cannot be empty.");
        }
    }

    /**
     * @param string $strIdentifier
     * @param int $intIdentifierType
     * @param array $arrElements
     * @throws InvalidArgumentException
     * @return string
     * Creates a new identifier that you specify with $strIdentifier. $intIdentifier specifies which type of
     * identifier you want to generate which will force required checks to be made for the specified
     * type. Each element in $arrElements will be submitted as individual metadata elements.
     */
    public function createIdentifier($strIdentifier, $intIdentifierType, array $arrElements) {
        if(is_string($strIdentifier) && $strIdentifier !== '') {
            if(is_int($intIdentifierType) && $intIdentifierType >= 1 && $intIdentifierType <= 2) {
                if(count($arrElements) > 0) {
                    $input = '';
                    foreach($arrElements as $key => $value) {
                        $input .= "$key: " .  ucfirst($value) . "\n";
                    }
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/id/'.$strIdentifier);
                    curl_setopt($ch, CURLOPT_USERPWD, "$this->strUserId:$this->strPassword");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array('Content-Type: text/plain; charset=UTF-8',
                            'Content-Length: ' . strlen($input)));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);
                    foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
                        if (strpos($line, 'success:') !== false) {
                            return $output;
                        } elseif (strpos($line, 'error:') !== false) {
                            throw new \LogicException($line);
                        }
                        break;
                    }

                }
                else {
                    throw new \InvalidArgumentException("arrElements cannot be empty.");
                }
            }
            else {
                throw new \InvalidArgumentException("Value $intIdentifierType not valid for intIdentifierType.");
            }
        }
        else {
            throw new \InvalidArgumentException("strIdentifier cannot be empty.");
        }
    }

    /**
     * @param string $strIdentifier
     * @param int $intIdentifierType
     * @param array $arrElements
     * @return string
     * @throws InvalidArgumentException
     * @throws LogicException
     * Modifies an identifier that you specify with $strIdentifier. $intIdentifier specifies which type of
     * identifier you want to modify which will force required checks to be made for the specified
     * type. Each element in $arrElements will be submitted as individual metadata elements and will overwrite
     * existing metadata.
     */
    public function modifyIdentifier($strIdentifier, $intIdentifierType, array $arrElements) {
        if(is_string($strIdentifier) && $strIdentifier !== '') {
            if(is_int($intIdentifierType) && $intIdentifierType >= 1 && $intIdentifierType <= 2) {
                if(count($arrElements) > 0) {
                    $input = '';
                    foreach($arrElements as $key => $value) {
                        $input .= "$key: " .  ucfirst($value) . "\n";
                    }
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/id/'.$strIdentifier);
                    curl_setopt($ch, CURLOPT_USERPWD, "$this->strUserId:$this->strPassword");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array('Content-Type: text/plain; charset=UTF-8',
                            'Content-Length: ' . strlen($input)));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);
                    foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
                        if (strpos($line, 'success:') !== false) {
                            return $output;
                        } elseif (strpos($line, 'error:') !== false) {
                            throw new \LogicException($line);
                        }
                        break;
                    }

                }
                else {
                    throw new \InvalidArgumentException("arrElements cannot be empty.");
                }
            }
            else {
                throw new \InvalidArgumentException("Value $intIdentifierType not valid for intIdentifierType.");
            }
        }
        else {
            throw new \InvalidArgumentException("strIdentifier cannot be empty.");
        }
    }

    /**
     * @param string $strIdentifier
     * @return string
     * @throws LogicException
     * @throws InvalidArgumentException
     * Deletes an identifier that you specify with $strIdentifier. Only reserved identifiers can be deleted.
     */
    public function deleteIdentifier($strIdentifier) {
        if(is_string($strIdentifier) && $strIdentifier !== '') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/id/'.$strIdentifier);
            curl_setopt($ch, CURLOPT_USERPWD, "$this->strUserId:$this->strPassword");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
            foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
                if (strpos($line, 'success:') !== false) {
                    return $output;
                } elseif (strpos($line, 'error:') !== false) {
                    throw new \LogicException($line);
                }
                break;
            }
        }
        else {
            throw new \InvalidArgumentException("strIdentifier cannot be empty.");
        }
    }
}
