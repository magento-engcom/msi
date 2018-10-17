<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Ui\DataProvider;

use Magento\Backend\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Ui\DataProvider\SearchResultFactory;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * @api
 */
class SourceDataProvider extends DataProvider
{
    const SOURCE_FORM_NAME = 'inventory_source_form_data_source';

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchResultFactory $searchResultFactory
     * @param Session $session
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) All parameters are needed for backward compatibility
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        SourceRepositoryInterface $sourceRepository,
        SearchResultFactory $searchResultFactory,
        Session $session,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->sourceRepository = $sourceRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->session = $session;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = parent::getData();
        if (self::SOURCE_FORM_NAME === $this->name) {
            // It is need for support of several fieldsets.
            // For details see \Magento\Ui\Component\Form::getDataSourceData
            if ($data['totalRecords'] > 0) {
                $sourceCode = $data['items'][0][SourceInterface::SOURCE_CODE];
                $sourceGeneralData = $data['items'][0];
                $dataForSingle[$sourceCode] = [
                    'general' => $sourceGeneralData,
                ];
                return $dataForSingle;
            }
            $sessionData = $this->session->getSourceFormData(true);
            if (null !== $sessionData) {
                // For details see \Magento\Ui\Component\Form::getDataSourceData
                $data = [
                    '' => $sessionData,
                ];
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResult()
    {
        $searchCriteria = $this->getSearchCriteria();
        $result = $this->sourceRepository->getList($searchCriteria);

        $searchResult = $this->searchResultFactory->create(
            $result->getItems(),
            $result->getTotalCount(),
            $searchCriteria,
            SourceInterface::SOURCE_CODE
        );
        return $searchResult;
    }
}
