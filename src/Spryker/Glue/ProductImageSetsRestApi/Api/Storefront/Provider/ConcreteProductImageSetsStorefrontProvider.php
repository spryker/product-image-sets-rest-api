<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductImageSetsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\ConcreteProductImageSetsStorefrontResource;
use Generated\Shared\Transfer\ProductConcreteStorageTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ProductImageStorage\ProductImageStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Glue\ProductImageSetsRestApi\Api\Storefront\Exception\ProductImageSetsExceptionFactory;

class ConcreteProductImageSetsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string URI_VAR_SKU = 'concreteProductSku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected ProductImageStorageClientInterface $productImageStorageClient,
        protected ProductImageSetsExceptionFactory $exceptionFactory = new ProductImageSetsExceptionFactory(),
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\ConcreteProductImageSetsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $sku = $this->resolveConcreteProductSku();

        $localeName = $this->getLocale()->getLocaleNameOrFail();
        $productConcreteData = $this->productStorageClient->findProductConcreteStorageDataByMapping(
            static::MAPPING_TYPE_SKU,
            $sku,
            $localeName,
        );

        if ($productConcreteData === null) {
            throw $this->exceptionFactory->createConcreteProductImageSetsNotFoundException();
        }

        $concreteTransfer = (new ProductConcreteStorageTransfer())->fromArray($productConcreteData, true);
        $imageStorageTransfers = $this->productImageStorageClient->resolveProductImageSetStorageTransfers(
            (int)$concreteTransfer->getIdProductConcrete(),
            (int)$concreteTransfer->getIdProductAbstract(),
            $localeName,
        );

        $imageSets = [];
        foreach ($imageStorageTransfers ?? [] as $imageStorageTransfer) {
            $imageSets[] = $imageStorageTransfer->toArray(true, true);
        }

        $resource = new ConcreteProductImageSetsStorefrontResource();
        $resource->concreteProductSku = $sku;
        $resource->imageSets = $imageSets;

        return [$resource];
    }

    protected function resolveConcreteProductSku(): string
    {
        if (!$this->hasUriVariable(static::URI_VAR_SKU)) {
            throw $this->exceptionFactory->createConcreteProductNotFoundException();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_SKU);

        if ($sku === '') {
            throw $this->exceptionFactory->createConcreteProductNotFoundException();
        }

        return $sku;
    }
}
