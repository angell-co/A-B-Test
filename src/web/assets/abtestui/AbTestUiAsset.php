<?php
/**
 * A/B Test  plugin for Craft CMS 3.x
 *
 * Run A/B tests easily in Craft.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2020 Angell & Co
 */

namespace angellco\abtest\web\assets\abtestui;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

/**
 * Asset bundle for A/B Test CP UI
 *
 * @author    Angell & Co
 * @package   AbTest
 * @since     1.0.0
 */
class AbTestUiAsset extends AssetBundle
{
    /**
     * @var bool
     */
    private $useDevServer = true;

    /**
     * @var bool
     */
    private $devServerBaseUrl = 'https://localhost:8080/';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist/';

        $this->depends = [
            CpAsset::class,
            VueAsset::class,
        ];

        if ($this->useDevServer) {
            $this->js = [
                $this->devServerBaseUrl . 'app.js',
            ];
        } else {
            $this->css = [
                'css/chunk-vendors.css',
                'css/app.css',
            ];

            $this->js = [
                'js/chunk-vendors.js',
                'js/app.js',
            ];
        }

        parent::init();
    }
}
