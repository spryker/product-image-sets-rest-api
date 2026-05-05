<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductImageSetsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\AbstractProductImageSetsStorefrontResource;
use Generated\Shared\Transfer\ProductAbstractImageStorageTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ProductImageStorage\ProductImageStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Glue\ProductImageSetsRestApi\Api\Storefront\Exception\ProductImageSetsExceptionFactory;

class AbstractProductImageSetsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    protected const string URI_VAR_SKU = 'abstractProductSku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected ProductImageStorageClientInterface $productImageStorageClient,
        protected ProductImageSetsExceptionFactory $exceptionFactory = new ProductImageSetsExceptionFactory(),
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\AbstractProductImageSetsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $sku = $this->resolveAbstractProductSku();
        $localeName = $this->getLocale()->getLocaleNameOrFail();

        $productAbstractData = $this->productStorageClient->findProductAbstractStorageDataByMapping(
            static::MAPPING_TYPE_SKU,
            $sku,
            $localeName,
        );

        if ($productAbstractData === null) {
            throw $this->exceptionFactory->createAbstractProductImageSetsNotFoundException();
        }

        $idProductAbstract = (int)($productAbstractData[static::KEY_ID_PRODUCT_ABSTRACT] ?? 0);
        $imageStorageTransfer = $this->productImageStorageClient->findProductImageAbstractStorageTransfer(
            $idProductAbstract,
            $localeName,
        ) ?? new ProductAbstractImageStorageTransfer();

        $resource = new AbstractProductImageSetsStorefrontResource();
        $resource->abstractProductSku = $sku;
        $resource->imageSets = $this->normalizeImageSets($imageStorageTransfer->toArray(true, true)['imageSets'] ?? []);

        return [$resource];
    }

    protected function resolveAbstractProductSku(): string
    {
        if (!$this->hasUriVariable(static::URI_VAR_SKU)) {
            throw $this->exceptionFactory->createAbstractProductNotFoundException();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_SKU);

        if ($sku === '') {
            throw $this->exceptionFactory->createAbstractProductNotFoundException();
        }

        return $sku;
    }

    /**
     * @param array<mixed> $imageSets
     *
     * @return array<mixed>
     */
    protected function normalizeImageSets(array $imageSets): array
    {
        return $imageSets;
    }
}
