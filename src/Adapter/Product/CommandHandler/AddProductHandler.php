<?php
/**
 * 2007-2020 PrestaShop SA and Contributors
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Product\CommandHandler;

use Category;
use PrestaShop\PrestaShop\Core\Domain\Product\Command\AddProductCommand;
use PrestaShop\PrestaShop\Core\Domain\Product\CommandHandler\AddProductHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductException;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;
use PrestaShopException;
use Product;

/**
 * Handles AddProductCommand using legacy object model
 */
final class AddProductHandler implements AddProductHandlerInterface
{
    /**
     * @var int
     */
    private $defaultLangId;

    /**
     * @var int
     */
    private $defaultCategoryId;

    /**
     * @param int $defaultLangId
     * @param int $defaultCategoryId
     */
    public function __construct(int $defaultLangId, int $defaultCategoryId)
    {
        $this->defaultLangId = $defaultLangId;
        $this->defaultCategoryId = $defaultCategoryId;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(AddProductCommand $command): ProductId
    {
        $product = $this->createProduct($command);

        try {
            if (!$product->add()) {
                throw new ProductException('Failed to add new basic product');
            }
            $product->updateCategories([$this->defaultCategoryId]);
        } catch (PrestaShopException $e) {
            throw new ProductException('Error occurred when trying to add new basic product.', 0, $e);
        }

        return new ProductId((int) $product->id);
    }

    /**
     * @param AddProductCommand $command
     *
     * @return Product
     */
    private function createProduct(AddProductCommand $command): Product
    {
        //@todo: multistore?
        $product = new Product();

        //@todo: product name is not required. Fix that? issue: #19441 for discussion
        $product->name = $command->getLocalizedNames();

        $product->active = false;
        $product->category = Category::getLinkRewrite($this->defaultCategoryId, $this->defaultLangId);
        $product->id_category_default = $this->defaultCategoryId;
        $product->is_virtual = $command->getType() === ProductType::TYPE_VIRTUAL;

        foreach ($product->name as $langId => $name) {
            if (true !== $product->validateField('name', $name, $langId)) {
                throw new ProductConstraintException(
                    sprintf(
                        'Invalid localized product name for language with id "%s"',
                        $langId
                    ),
                    ProductConstraintException::INVALID_NAME
                );
            }
        }

        return $product;
    }
}
