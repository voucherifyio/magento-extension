<?php
namespace Voucherify\Integration\Model;

use Voucherify\Integration\Api\VoucherDataRepositoryInterface;
use Voucherify\Integration\Model\ResourceModel\VoucherData as VoucherDataResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class VoucherDataRepository
 * @package Voucherify\Integration\Model
 */
class VoucherDataRepository implements VoucherDataRepositoryInterface
{
    /**
     * @var VoucherDataFactory
     */
    protected $voucherDataFactory;

    /**
     * @var VoucherDataResource
     */
    protected $resource;

    /**
     * @param VoucherDataFactory $voucherDataFactory
     * @param VoucherDataResource $voucherDataResource
     */
    public function __construct(
        VoucherDataFactory $voucherDataFactory,
        VoucherDataResource $voucherDataResource
    ) {
        $this->voucherDataFactory = $voucherDataFactory;
        $this->resource = $voucherDataResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($voucherId)
    {
        $voucherData = $this->voucherDataFactory->create();
        $this->resource->load($voucherData, $voucherId);
        return $voucherData;
    }

    /**
     * {@inheritdoc}
     */
    public function getByQuoteId($quoteId)
    {
        $voucherData = $this->voucherDataFactory->create();
        $this->resource->load($voucherData, $quoteId, 'quote_id');
        return $voucherData;
    }

    /**
     * {@inheritdoc}
     */
    public function save(VoucherData $voucherData)
    {
        try {
            $this->resource->save($voucherData);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $voucherData;
    }


    /**
     * {@inheritdoc}
     */
    public function delete(VoucherData $voucherData)
    {
        try {
            $this->resource->delete($voucherData);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($voucherId)
    {
        return $this->delete($this->getById($voucherId));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByQuoteId($quoteId)
    {
        return $this->delete($this->getByQuoteId($quoteId));
    }
}
