<?php

/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace Tests\Integration\Core\Localization;

use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use PrestaShop\PrestaShop\Core\Localization\Locale\Repository as LocaleRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tests\TestCase\SymfonyIntegrationTestCase;

class LocaleUsageTest extends SymfonyIntegrationTestCase
{
    const SERVICE_LOCALE_REPOSITORY  = 'prestashop.core.localization.locale.repository';
    const SERVICE_CLDR_CACHE_ADAPTER = 'prestashop.core.localization.cldr.cache.adapter';

    /**
     * The Locale repository is the entry point to retrieve a given Locale object.
     * Then the Locale object is the entry point to formatting numbers and prices.
     *
     * @var LocaleRepository
     */
    protected $localeRepository;

    private static $isCacheClear = false;

    protected function setUp()
    {
        parent::setUp();

        if (!self::$isCacheClear) {
            $this->clearCache();
            self::$isCacheClear = true;
        }

        $this->localeRepository = $this->container->get(self::SERVICE_LOCALE_REPOSITORY);
    }

    protected function clearCache()
    {
        /** @var FilesystemAdapter $cacheAdapter */
        $cacheAdapter = self::$kernel->getContainer()->get(self::SERVICE_CLDR_CACHE_ADAPTER);
        $cacheAdapter->clear();
    }

    /**
     * Given a valid Locale object
     * When asking this Locale object to format a number according to its specific formatting rules
     * Then the correct formatted number should be retrieved
     *
     * @dataProvider provideLocalizedNumbers
     *
     * @param $localeCode
     * @param $rawNumber
     * @param $formattedNumber
     *
     * @throws LocalizationException
     */
    public function testItShouldFormatNumbers($localeCode, $rawNumber, $formattedNumber)
    {
        $locale = $this->localeRepository->getLocale($localeCode);

        $this->assertSame(
            $formattedNumber,
            $locale->formatNumber($rawNumber)
        );
    }

    public function provideLocalizedNumbers()
    {
        return [
            'United States'    => [
                'localeCode'      => 'en-US',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'Japan'            => [
                'localeCode'      => 'ja-JP',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'United Kingdom'   => [
                'localeCode'      => 'en-GB',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'Germany'          => [
                'localeCode'      => 'de-DE',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1.234.568,123',
            ],
            'France'           => [
                'localeCode'      => 'fr-FR',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1 234 568,123',
            ],
            'India (Hindi)'    => [
                'localeCode'      => 'hi-IN',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '12,34,568.123',
            ],
            'India (English)'  => [
                'localeCode'      => 'en-IN',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '12,34,568.123',
            ],
            'India (Bengali)'  => [
                'localeCode'      => 'bn-IN',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '12,34,568.123',
            ],
            'Spain'            => [
                'localeCode'      => 'es-ES',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1.234.568,123',
            ],
            'Canada (French)'  => [
                'localeCode'      => 'fr-CA',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1 234 568,123',
            ],
            'Canada (English)' => [
                'localeCode'      => 'en-CA',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'China'            => [
                'localeCode'      => 'zh-CN',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'Australia'        => [
                'localeCode'      => 'en-AU',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'Brazil'           => [
                'localeCode'      => 'pt-BR',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1.234.568,123',
            ],
            'Mexico'           => [
                'localeCode'      => 'es-MX',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1,234,568.123',
            ],
            'Russia'           => [
                'localeCode'      => 'ru-RU',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1 234 568,123',
            ],
            'Italy'            => [
                'localeCode'      => 'it-IT',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1.234.568,123',
            ],
            'Poland'           => [
                'localeCode'      => 'pl-PL',
                'rawNumber'       => 1234568.12345,
                'formattedNumber' => '1 234 568,123',
            ],
        ];
    }
}
