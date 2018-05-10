<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 27/09/2017
 * Time: 16:13
 */

namespace AppBundle\Translater;


interface TranslaterInterface
{
    public function load(string $domain, string $locale);

    public function getTranslation(string $key, array $array=[]);
}