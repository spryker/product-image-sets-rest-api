<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductImageSetsRestApi\Processor\Reader;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface AbstractProductImageSetsReaderInterface
{
    public function getAbstractProductImageSets(RestRequestInterface $restRequest): RestResponseInterface;

    public function findAbstractProductImageSetsBySku(string $sku, RestRequestInterface $restRequest): ?RestResourceInterface;
}
