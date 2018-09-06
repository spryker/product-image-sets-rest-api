<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductImageSetsRestApi\Dependency\Client;

use Generated\Shared\Transfer\ProductAbstractImageStorageTransfer;
use Generated\Shared\Transfer\ProductConcreteImageStorageTransfer;

class ProductImageSetsRestApiToProductImageStorageClientBridge implements ProductImageSetsRestApiToProductImageStorageClientInterface
{
    /**
     * @var \Spryker\Client\ProductImageStorage\ProductImageStorageClientInterface
     */
    protected $productImageStorageClient;

    /**
     * @param \Spryker\Client\ProductImageStorage\ProductImageStorageClientInterface $productImageStorageClient
     */
    public function __construct($productImageStorageClient)
    {
        $this->productImageStorageClient = $productImageStorageClient;
    }

    /**
     * @api
     *
     * @param int $idProductAbstract
     * @param string $locale
     *
     * @return \Generated\Shared\Transfer\ProductAbstractImageStorageTransfer|null
     */
    public function findProductImageAbstractStorageTransfer($idProductAbstract, $locale): ?ProductAbstractImageStorageTransfer
    {
        return $this->productImageStorageClient->findProductImageAbstractStorageTransfer($idProductAbstract, $locale);
    }

    /**
     * @api
     *
     * @param int $idProductConcrete
     * @param string $locale
     *
     * @return \Generated\Shared\Transfer\ProductConcreteImageStorageTransfer|null
     */
    public function findProductImageConcreteStorageTransfer($idProductConcrete, $locale): ?ProductConcreteImageStorageTransfer
    {
        return $this->productImageStorageClient->findProductImageConcreteStorageTransfer($idProductConcrete, $locale);
    }
}
