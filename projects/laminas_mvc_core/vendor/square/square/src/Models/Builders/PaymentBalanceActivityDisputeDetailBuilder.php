<?php

declare(strict_types=1);

namespace Square\Models\Builders;

use Core\Utils\CoreHelper;
use Square\Models\PaymentBalanceActivityDisputeDetail;

/**
 * Builder for model PaymentBalanceActivityDisputeDetail
 *
 * @see PaymentBalanceActivityDisputeDetail
 */
class PaymentBalanceActivityDisputeDetailBuilder
{
    /**
     * @var PaymentBalanceActivityDisputeDetail
     */
    private $instance;

    private function __construct(PaymentBalanceActivityDisputeDetail $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new payment balance activity dispute detail Builder object.
     */
    public static function init(): self
    {
        return new self(new PaymentBalanceActivityDisputeDetail());
    }

    /**
     * Sets payment id field.
     */
    public function paymentId(?string $value): self
    {
        $this->instance->setPaymentId($value);
        return $this;
    }

    /**
     * Unsets payment id field.
     */
    public function unsetPaymentId(): self
    {
        $this->instance->unsetPaymentId();
        return $this;
    }

    /**
     * Sets dispute id field.
     */
    public function disputeId(?string $value): self
    {
        $this->instance->setDisputeId($value);
        return $this;
    }

    /**
     * Unsets dispute id field.
     */
    public function unsetDisputeId(): self
    {
        $this->instance->unsetDisputeId();
        return $this;
    }

    /**
     * Initializes a new payment balance activity dispute detail object.
     */
    public function build(): PaymentBalanceActivityDisputeDetail
    {
        return CoreHelper::clone($this->instance);
    }
}
