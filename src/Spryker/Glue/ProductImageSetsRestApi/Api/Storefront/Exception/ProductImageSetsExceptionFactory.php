<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductImageSetsRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\ProductImageSetsRestApi\ProductImageSetsRestApiConfig;
use Spryker\Glue\ProductsRestApi\ProductsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ProductImageSetsExceptionFactory
{
    public function createAbstractProductImageSetsNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductImageSetsRestApiConfig::RESPONSE_CODE_ABSTRACT_PRODUCT_IMAGE_SETS_NOT_FOUND,
            ProductImageSetsRestApiConfig::RESPONSE_DETAIL_ABSTRACT_PRODUCT_IMAGE_SETS_NOT_FOUND,
        );
    }

    public function createConcreteProductImageSetsNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductImageSetsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_IMAGE_SETS_NOT_FOUND,
            ProductImageSetsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_IMAGE_SETS_NOT_FOUND,
        );
    }

    public function createAbstractProductNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductsRestApiConfig::RESPONSE_CODE_CANT_FIND_ABSTRACT_PRODUCT,
            ProductsRestApiConfig::RESPONSE_DETAIL_CANT_FIND_ABSTRACT_PRODUCT,
        );
    }

    public function createConcreteProductNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            ProductsRestApiConfig::RESPONSE_CODE_CANT_FIND_CONCRETE_PRODUCT,
            ProductsRestApiConfig::RESPONSE_DETAIL_CANT_FIND_CONCRETE_PRODUCT,
        );
    }
}
