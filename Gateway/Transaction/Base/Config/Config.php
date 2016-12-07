<?php

namespace Webjump\BraspagPagador\Gateway\Transaction\Base\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Braspag Transaction Base Config
 *
 * @author      Webjump Core Team <dev@webjump.com>
 * @copyright   2016 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */
class Config extends AbstractConfig implements ConfigInterface
{
	protected $config;

	public function __construct(
		ScopeConfigInterface $config
	){
		$this->setConfig($config);
	}

	public function getMerchantId()
	{
		return $this->_getConfig(self::CONFIG_XML_BRASPAG_PAGADOR_GLOBAL_MERCHANT_ID);
	}

	public function getMerchantKey()
	{
		return $this->_getConfig(self::CONFIG_XML_BRASPAG_PAGADOR_GLOBAL_MERCHANT_KEY);
	}
}