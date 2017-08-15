<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * TODO: more clear description
 * Chain of validators. Extension point for new validators via di configuration
 */
class ValidatorChain implements SourceValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var SourceValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceValidatorInterface[] $validators
     * @throws LocalizedException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;

        foreach ($validators as $validator) {
            if (!$validator instanceof SourceValidatorInterface) {
                throw new LocalizedException(
                    __('Source Validator must implement SourceValidatorInterface.')
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(SourceInterface $source)
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($source);

            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
            }
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
