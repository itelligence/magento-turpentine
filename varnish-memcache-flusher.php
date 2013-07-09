<?php
require_once('/sites/magento/app/Mage.php');
umask(0);
Mage::app();

/*

Varnish Memcache Flusher: by Tegan Snyder
This PHP file exists on all minions and is executed every 60 seconds by a cronjob on Magento Admin Server.
The Admin/staging script uses SaltStack to run the php file across the minions.

The cron script runs as root:
/bin/MemcacheVarnishFlusher
#!/bin/bash
salt '*' cmd.run 'php /full/path/to/varnish-memcache-flusher.php'

Using Nexcessnet Turpentine extension for Varnish funcationalty improvements in Magento I have modified:
app/code/community/Nexcessnet/Turpentine/Model/Observer/Ban.php 
I added some memcache ability to that file.



// admin/staging server:

$hostname = gethostname(); // needed for PHP CLI

if ($hostname == 'my_magent_admins_hostname_from_variable_above') {

	echo 'this script doesnt run on admin/staging';

// minion servers:

} else {

	$node = 'MY_MEMCACHE_NODE_IP';
	$cache = new Memcache;
	$cache->connect($node, 11211);

	$varnish_ban = $cache->get('varnish_ban');
	$cache->close();

	if (isset($varnish_ban['type'])) {

		if (Mage::helper('turpentine/varnish')->getVarnishEnabled()) {

			if ($varnish_ban['type'] == 'banCmsPageCache') {

				if (isset($varnish_ban['pageId'])) {

					$pageId = $varnish_ban['pageId'];

					$result = Mage::getModel('turpentine/varnish_admin')->flushUrl($pageId . '(?:\.html?)?$');

			        Mage::dispatchEvent('turpentine_ban_cms_page_cache', $result);

		    	}

			} elseif ($varnish_ban['type'] == 'banAllCache') {

				$result = Mage::getModel('turpentine/varnish_admin')->flushAll();

	            Mage::dispatchEvent('turpentine_ban_all_cache', $result);

			} elseif ($varnish_ban['type'] == 'banProductPageCache') {

				if (isset($varnish_ban['urlPattern'])) {

					$urlPattern = $varnish_ban['urlPattern'];

					$result = Mage::getModel('turpentine/varnish_admin')->flushUrl($urlPattern);
	            
	            	Mage::dispatchEvent('turpentine_ban_product_cache', $result);

	        	}

			} elseif ($varnish_ban['type'] == 'banProductReview') {

				if (isset($varnish_ban['urlPattern'])) {

					$urlPattern = $varnish_ban['urlPattern'];

					$result = Mage::getModel('turpentine/varnish_admin')->flushUrl($urlPattern);
	            
	            	Mage::dispatchEvent('turpentine_ban_product_cache', $result);

	        	}

			} elseif ($varnish_ban['type'] == 'banProductPageCacheCheckStock') {

				if (isset($varnish_ban['urlPattern'])) {

					$urlPattern = $varnish_ban['urlPattern'];

					$result = Mage::getModel('turpentine/varnish_admin')->flushUrl($urlPattern);
	            
	            	Mage::dispatchEvent('turpentine_ban_product_cache', $result);

	        	}

			} elseif ($varnish_ban['type'] == 'banCategoryCache') {

				if (isset($varnish_ban['UrlKey'])) {

					$UrlKey = $varnish_ban['UrlKey'];

					$result = Mage::getModel('turpentine/varnish_admin')->flushUrl($UrlKey);
	            
	            	Mage::dispatchEvent('turpentine_ban_product_cache', $result);

	        	}

			} elseif ($varnish_ban['type'] == 'banMediaCache') {

				$result = Mage::getModel('turpentine/varnish_admin')->flushUrl('media/(?:js|css)/');

			} elseif ($varnish_ban['type'] == 'banCatalogImagesCache') {

				$result = Mage::getModel('turpentine/varnish_admin')->flushUrl('media/catalog/product/cache/');

                Mage::dispatchEvent('turpentine_ban_catalog_images_cache', $result);

			}

		}

	}

}
