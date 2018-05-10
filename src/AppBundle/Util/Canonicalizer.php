<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 11/09/2017
 * Time: 10:42
 */

namespace AppBundle\Util;


class Canonicalizer implements CanonicalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function canonicalize($string)
    {
        if (null === $string) {
            return null;
        }
        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);
        return $result;
    }
}