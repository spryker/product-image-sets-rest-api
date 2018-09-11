<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductImageSetsRestApi\Processor\ConcreteProductsImageSets;

use Generated\Shared\Transfer\ProductConcreteImageStorageTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\ProductImageSetsRestApi\Dependency\Client\ProductImageSetsRestApiToProductImageStorageClientInterface;
use Spryker\Glue\ProductImageSetsRestApi\Dependency\Client\ProductImageSetsRestApiToProductStorageClientInterface;
use Spryker\Glue\ProductImageSetsRestApi\Processor\Mapper\ConcreteProductImageSetsMapperInterface;
use Spryker\Glue\ProductImageSetsRestApi\ProductImageSetsRestApiConfig;
use Spryker\Glue\ProductsRestApi\ProductsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class ConcreteProductImageSetsReader implements ConcreteProductImageSetsReaderInterface
{
    protected const PRODUCT_CONCRETE_MAPPING_TYPE = 'sku';
    protected const KEY_ID_PRODUCT_CONCRETE = 'id_product_concrete';
    protected const SELF_LINK_FORMAT = '%s/%s/%s';

    /**
     * @var \Spryker\Glue\ProductImageSetsRestApi\Dependency\Client\ProductImageSetsRestApiToProductStorageClientInterface
     */
    protected $productStorageClient;

    /**
     * @var \Spryker\Glue\ProductImageSetsRestApi\Dependency\Client\ProductImageSetsRestApiToProductImageStorageClientInterface
     */
    protected $productImageStorageClient;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\ProductImageSetsRestApi\Processor\Mapper\ConcreteProductImageSetsMapperInterface
     */
    protected $concreteProductImageSetsMapper;

    /**
     * @param \Spryker\Glue\ProductImageSetsRestApi\Dependency\Client\ProductImageSetsRestApiToProductStorageClientInterface $productStorageClient
     * @param \Spryker\Glue\ProductImageSetsRestApi\Dependency\Client\ProductImageSetsRestApiToProductImageStorageClientInterface $productImageStorageClient
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResponseBuilder
     * @param \Spryker\Glue\ProductImageSetsRestApi\Processor\Mapper\ConcreteProductImageSetsMapperInterface $concreteProductImageSetsMapper
     */
    public function __construct(
        ProductImageSetsRestApiToProductStorageClientInterface $productStorageClient,
        ProductImageSetsRestApiToProductImageStorageClientInterface $productImageStorageClient,
        RestResourceBuilderInterface $restResponseBuilder,
        ConcreteProductImageSetsMapperInterface $concreteProductImageSetsMapper
    ) {
        $this->productStorageClient = $productStorageClient;
        $this->productImageStorageClient = $productImageStorageClient;
        $this->restResourceBuilder = $restResponseBuilder;
        $this->concreteProductImageSetsMapper = $concreteProductImageSetsMapper;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getConcreteProductImageSets(RestRequestInterface $restRequest): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $parentResource = $restRequest->findParentResourceByType(ProductsRestApiConfig::RESOURCE_CONCRETE_PRODUCTS);
        if (!$parentResource) {
            return $restResponse->addError(
                $this->createConcreteProductNotFoundError()
            );
        }

        $concreteSku = $parentResource->getId();
        $restResource = $this->findConcreteProductImageSetsBySku($concreteSku, $restRequest);

        if ($restResource === null) {
            return $restResponse->addError(
                $this->createConcreteProductImageSetsNotFoundError()
            );
        }

        return $restResponse->addResource($restResource);
    }

    /**
     * @param string $sku
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface|null
     */
    public function findConcreteProductImageSetsBySku(string $sku, RestRequestInterface $restRequest): ?RestResourceInterface
    {
        $concreteProductData = $this->productStorageClient
            ->findProductConcreteStorageDataByMapping(
                static::PRODUCT_CONCRETE_MAPPING_TYPE,
                $sku,
                $restRequest->getMetadata()->getLocale()
            );

        if (!$concreteProductData) {
            return null;
        }

        $productImageConcreteStorageTransfer = $this->productImageStorageClient
            ->findProductImageConcreteStorageTransfer($concreteProductData[static::KEY_ID_PRODUCT_CONCRETE], $restRequest->getMetadata()->getLocale());

        if (!$productImageConcreteStorageTransfer) {
            return null;
        }

        return $this->buildProductImageSetsResource($sku, $productImageConcreteStorageTransfer);
    }

    /**
     * @param string $sku
     * @param \Generated\Shared\Transfer\ProductConcreteImageStorageTransfer $productImageConcreteStorageTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    protected function buildProductImageSetsResource(string $sku, ProductConcreteImageStorageTransfer $productImageConcreteStorageTransfer): RestResourceInterface
    {
        $restProductConcreteImageSetAttributesTransfer = $this->concreteProductImageSetsMapper
            ->mapProductConcreteImageStorageTransferToRestProductImageSetsAttributesTransfer($productImageConcreteStorageTransfer);

        $restResource = $this->restResourceBuilder->createRestResource(
            ProductImageSetsRestApiConfig::RESOURCE_CONCRETE_PRODUCT_IMAGE_SETS,
            $sku,
            $restProductConcreteImageSetAttributesTransfer
        );

        $restResourceSelfLink = sprintf(
            static::SELF_LINK_FORMAT,
            ProductsRestApiConfig::RESOURCE_CONCRETE_PRODUCTS,
            $sku,
            ProductImageSetsRestApiConfig::RESOURCE_CONCRETE_PRODUCT_IMAGE_SETS
        );
        $restResource->addLink(RestResourceInterface::RESOURCE_LINKS_SELF, $restResourceSelfLink);

        return $restResource;
    }

    /**
     * @return \Generated\Shared\Transfer\RestErrorMessageTransfer
     */
    protected function createConcreteProductNotFoundError(): RestErrorMessageTransfer
    {
        return (new RestErrorMessageTransfer())
            ->setCode(ProductsRestApiConfig::RESPONSE_CODE_CANT_FIND_CONCRETE_PRODUCT)
            ->setStatus(Response::HTTP_NOT_FOUND)
            ->setDetail(ProductsRestApiConfig::RESPONSE_DETAIL_CANT_FIND_CONCRETE_PRODUCT);
    }

    /**
     * @return \Generated\Shared\Transfer\RestErrorMessageTransfer
     */
    protected function createConcreteProductImageSetsNotFoundError(): RestErrorMessageTransfer
    {
        $restErrorTransfer = (new RestErrorMessageTransfer())
            ->setCode(ProductImageSetsRestApiConfig::RESPONSE_CODE_CONCRETE_PRODUCT_IMAGE_SETS_NOT_FOUND)
            ->setStatus(Response::HTTP_NOT_FOUND)
            ->setDetail(ProductImageSetsRestApiConfig::RESPONSE_DETAIL_CONCRETE_PRODUCT_IMAGE_SETS_NOT_FOUND);

        return $restErrorTransfer;
    }
}
