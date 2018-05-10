<?php
namespace AppBundle\Service;

use Doctrine\ORM\Event\LifecycleEventArgs;

class PasswordService
{

    /**
     * @param string $input
     * @return bool|string
     */
   public function generateHash(string $input){
       return password_hash($input, PASSWORD_DEFAULT);
   }

    /**
     * @param string $input
     * @param string $hash
     * @return bool
     */
   public function verifyHash(string $input, string $hash){
       return password_verify($input, $hash);
   }

    /**
     * @param string $input
     * @return int
     */
   public function calculateStrength(string $input){
       $score = 0;
       $iLength = strlen($input);
       switch($iLength) {
           case 0:
               return $score;
           case $iLength < 4:
               $score = 30;
               break;
           case $iLength < 8:
               $score = 50;
               break;
           case $iLength > 8:
               $score = 60;
               break;
       }

       if(strtolower($input) != $input && strtoupper($input) != $input) {
           $score += 5;
       }

       preg_match_all('/[0-9]/', $input, $numbers);
       $nCount = count($numbers[0]);

       if($nCount != $iLength && $nCount > 0){
           $score += 5;
       }

       preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $input, $specialchars);
       $sCount = count($specialchars[0]);

       if($sCount != $iLength && $sCount > 0){
           $score += 5;
       }

       $chars = str_split($input);
       $uCount = count(array_unique($chars));
       $score = ($score + $uCount);

       $ratios['ni'] = $nCount / $iLength;
       $ratios['si'] = $sCount / $iLength;
       $ratios['ui'] = $uCount / $iLength;

       foreach($ratios as $aRatio){
           $score = (int) floor($score * (1 + $aRatio));
       }

       return $score;
   }

    /**
     * @param string $input
     * @param int $minStrength
     * @return bool
     */
   public function validateStrength(string $input, $minStrength = 70){
       $score = $this->calculateStrength($input);

       if($score < $minStrength){
           return false;
       }

       return true;
   }

    /**
     * @param int $length
     * @param bool $includeSpecialChars
     * @param string|null $userDictionary
     * @return string
     */
    function generateRandom(int $length = 8, bool $includeSpecialChars = true, string $userDictionary = null) {
        $dictionary = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $specialChars = '!.,#%$/{}*+-()[]?_-@;~';
        if($includeSpecialChars){
            $dictionary .= $specialChars;
        }
        if(!is_null($userDictionary)){
            $dictionary .= $userDictionary;
        }
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, strlen($dictionary));
            $password .= $dictionary[$n];
        }
        return $password;
    }

    /**
     * Pin generator - Only digits
     *
     * @param int $length
     * @param bool $startWithZero
     * @return string
     */
    public function generatePin(int $length = 6, bool $startWithZero = false)
    {
        $data = [];

        for($i=0; $i<$length; $i++) {
            $min = 0;
            if (!$startWithZero && $i == 0) {
                $min = 1;
            }
            $data[$i] = mt_rand($min,9);
        }

        return implode("", $data);
    }
}