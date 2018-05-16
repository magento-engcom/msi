<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceDeduction;

use Magento\InventoryShipping\Model\SourceDeduction\Request\SourceDeductionRequestInterface;

/**
 * Process source deduction
 *
 * @api
 */
interface SourceDeductionServiceInterface
{
    /**
     * @param SourceDeductionRequestInterface $sourceDeductionRequest
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(SourceDeductionRequestInterface $sourceDeductionRequest): void;
}
