<?php
/**
 * Created by BoDev Office.
 * User: Erman Titiz ( @ermantitiz )
 * Date: 10/10/2017
 * Time: 13:24
 */

namespace AppBundle\Translater;
use Symfony\Component\Yaml\Yaml;
class ExceptionTranslater extends AbstractTranslater
{

    public $messages = [];


    /**
     * @param string $domain
     * @param string $locale
     * @return array
     */
    public function load(string $domain = null, string $locale = 'tr')
    {

        if(is_null($domain))
        {
            $domain = __DIR__ . '/../Resources/translations/exception';
        }

        $yamlContents = Yaml::parse(file_get_contents($domain . '.yml'));
        foreach ($yamlContents as $key => $translatedValues) {
            $this->messages[$key] = isset($translatedValues[$locale]) ? $translatedValues[$locale] : '';
        }

        return $this->messages;
    }

    /**
     * @param string $key
     * @param array $replacementArray
     * @return \InvalidArgumentException|mixed
     */
    public function getTranslation(string $key, array $replacementArray = [])
    {

        if(!key_exists($key,$this->messages)) return $key;
        if (count($replacementArray) > 0) {
            $pattern = '/[\?][\?]/';
            preg_match_all($pattern, $this->messages[$key], $matches);
            $matchCount = count($matches);
            if ($matchCount > 0 && $matchCount != count($replacementArray)) {
                // TODO throw Exception
            }
            $message = $this->messages[$key];
            if ($matchCount > 0) {
                foreach ($replacementArray as $toReplace) {
                    $message = preg_replace($pattern, $toReplace, $message, 1);
                }
            }
        } else {
            if(count($this->messages)==0) $this->load();
            $message = array_key_exists($key, $this->messages) ? $this->messages[$key] : null;
        }

        return $message;

    }
}

