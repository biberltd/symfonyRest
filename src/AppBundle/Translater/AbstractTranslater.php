<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 27/09/2017
 * Time: 16:11
 */

namespace AppBundle\Translater;


abstract class AbstractTranslater implements TranslaterInterface
{

    public $messages = [];

    /**
     * get the repository for the current controller
     * @return mixed
     */
    abstract public function load(string $domain, string $locale);

    /**
     * get the repository for the current controller
     * @return mixed
     */
    abstract public function getTranslation(string $key, array $array=[]);


    /**
     *  Constructor.
     * @param string $domain
     * @param string $locale
     */
    public function __construct(string $domain = null, string $locale = 'tr')
    {
        if(!is_null($domain))
        $this->messages = $this->load($domain, $locale);
    }
}