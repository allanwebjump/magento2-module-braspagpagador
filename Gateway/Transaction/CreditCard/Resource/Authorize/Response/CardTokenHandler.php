<?php

namespace Webjump\BraspagPagador\Gateway\Transaction\CreditCard\Resource\Authorize\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Webjump\Braspag\Pagador\Transaction\Api\CreditCard\Send\ResponseInterface;
use Webjump\BraspagPagador\Api\CardTokenRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\SearchCriteriaBuilder;


/**
 * Braspag Transaction CreditCard Authorize Response Handler
 *
 * @author      Webjump Core Team <dev@webjump.com>
 * @copyright   2016 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */
class CardTokenHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @var
     */
    protected $cardTokenRepository;

    /**
     * @var
     */
    protected $eventManager;

    /**
     * @var
     */
    protected $searchCriteriaBuilder;

    /**
     * @var
     */
    protected $cardTokenId = null;


    /**
     * CardTokenHandler constructor.
     *
     * @param CardTokenRepositoryInterface $cardTokenRepository
     * @param ManagerInterface             $eventManager
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder
     */
    public function __construct(
        CardTokenRepositoryInterface $cardTokenRepository,
        ManagerInterface $eventManager,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->setCardTokenRepository($cardTokenRepository);
        $this->setEventManager($eventManager);
        $this->setSearchCriteriaBuilder($searchCriteriaBuilder);
    }

    /**
     * @param $payment
     * @param $response
     *
     * @return $this
     */
    protected function _handle($payment, $response)
    {
        if ($response->getPaymentCardToken()) {
            $this->saveCardToken($payment, $response);
        }

        return $this;
    }

    /**
     * @param $payment
     * @param $response
     *
     * @return mixed
     */
    protected function saveCardToken($payment, $response)
    {
        if ($cardToken = $this->getCardTokenRepository()->get($response->getPaymentCardToken())) {
            return $cardToken;
        }
        $searchCriteriaBuilder = $this->getSearchCriteriaBuilder();
        $searchCriteriaBuilder->addFilter('method', $payment->getMethod());
        $searchCriteriaBuilder->addFilter('customer_id', $payment->getOrder()->getCustomerId());
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->getCardTokenRepository()->getList($searchCriteria);

        foreach ($searchResult->getItems() as $item) {
            $this->getCardTokenRepository()->delete($item);
        }

        $data = new DataObject([
            'alias' => $payment->getCcNumber(),
            'token' => $response->getPaymentCardToken(),
            'provider' => $response->getPaymentCardProvider(),
            'brand' => $response->getPaymentCardBrand(),
        ]);

        $this->getEventManager()->dispatch(
            'braspag_creditcard_token_handler_save_before',
            ['card_data' => $data, 'payment' => $payment, 'response' => $response]
        );

        $cardToken = $this->getCardTokenRepository()->create($data->toArray());

        $this->getCardTokenRepository()->save($cardToken);

        return $cardToken;
    }

    /**
     * @return mixed
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * @param mixed $searchCriteriaBuilder
     */
    public function setSearchCriteriaBuilder($searchCriteriaBuilder)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return mixed
     */
    protected function getCardTokenRepository()
    {
        return $this->CardTokenRepository;
    }

    /**
     * @param CardTokenRepositoryInterface $cardTokenRepository
     *
     * @return $this
     */
    protected function setCardTokenRepository(CardTokenRepositoryInterface $cardTokenRepository)
    {
        $this->CardTokenRepository = $cardTokenRepository;

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @param $eventManager
     *
     * @return $this
     */
    protected function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }
}
