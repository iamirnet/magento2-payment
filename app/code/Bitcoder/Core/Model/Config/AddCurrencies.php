<?php

namespace Bitcoder\Core\Model\Config;

use Magento\Framework\Locale\Bundle\CurrencyBundle;
use \Magento\Framework\Locale\TranslatedLists;
class AddCurrencies extends TranslatedLists
{


    /**
     * @return array
     */
    public function getOptionAllCurrencies()
    {
        $currencyBundle = new \Magento\Framework\Locale\Bundle\CurrencyBundle();
        $locale = $this->localeResolver->getLocale();
        $currencies = $currencyBundle->get($locale)['Currencies'] ?: [];

        $options = [];
        foreach ($currencies as $code => $data) {
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        return $this->_sortOptionArray(array_merge($options, $this->getNewCurrencies()));

    }

    public function getNewCurrencies()

    {
        return [
            ['value' => 'IRT', 'label' => 'Toman'],
        ];

    }

    /**
     * @return array
     */
    public function getOptionCurrencies()
    {
        $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'] ?: [];
        $options = [];
        $allowed = $this->_config->getAllowedCurrencies();
        foreach ($currencies as $code => $data) {
            if (!in_array($code, $allowed)) {
                continue;
            }
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        return $this->_sortOptionArray(array_merge($options, $this->getNewCurrencies()));

    }
}
