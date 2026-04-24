<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductImageSetsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\AbstractProductImageSetsStorefrontResource;
use Generated\Shared\Transfer\ProductAbstractImageStorageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\ProductImageStorage\ProductImageStorageClientInterface;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Glue\ProductImageSetsRestApi\ProductImageSetsRestApiConfig;
use Spryker\Glue\ProductsRestApi\ProductsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class AbstractProductImageSetsStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_SKU = 'sku';

    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    protected const string URI_VAR_SKU = 'abstractProductSku';

    public function __construct(
        protected ProductStorageClientInterface $productStorageClient,
        protected ProductImageStorageClientInterface $productImageStorageClient,
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
            throw new GlueApiException(
                Response::HTTP_NOT_FOUND,
                ProductImageSetsRestApiConfig::RESPONSE_CODE_ABSTRACT_PRODUCT_IMAGE_SETS_NOT_FOUND,
                ProductImageSetsRestApiConfig::RESPONSE_DETAIL_ABSTRACT_PRODUCT_IMAGE_SETS_NOT_FOUND,
            );
        }

        $idProductAbstract = (int)($productAbstractData[static::KEY_ID_PRODUCT_ABSTRACT] ?? 0);
        $imageStorageTransfer = $this->productImageStorageClient->findProductImageAbstractStorageTransfer(
            $idProductAbstract,
            $localeName,
        ) ?? new ProductAbstractImageStorageTransfer();

        $resource = new AbstractProductImageSetsStorefrontResource();
        $resource->abstractProductSku = $sku;
        $resource->imageSets = $this->normalizeImageSets($imageStorageTransfer->toArray()['imageSets'] ?? []);

        return [$resource];
    }

    protected function resolveAbstractProductSku(): string
    {
        if (!$this->hasUriVariable(static::URI_VAR_SKU)) {
            $this->throwAbstractProductNotFound();
        }

        $sku = (string)$this->getUriVariable(static::URI_VAR_SKU);

        if ($sku === '') {
            $this->throwAbstractProductNotFound();
        }

        return $sku;
    }

    protected function throwAbstractProductNotFound(): never
    {
        throw new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductsRestApiConfig::RESPONSE_CODE_CANT_FIND_ABSTRACT_PRODUCT,
            ProductsRestApiConfig::RESPONSE_DETAIL_CANT_FIND_ABSTRACT_PRODUCT,
        );
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
