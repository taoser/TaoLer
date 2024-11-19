<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\EasySms\Contracts;

/**
 * Interface MessageInterface.
 */
interface MessageInterface
{
<<<<<<< HEAD
    const TEXT_MESSAGE = 'text';

    const VOICE_MESSAGE = 'voice';
=======
    public const TEXT_MESSAGE = 'text';

    public const VOICE_MESSAGE = 'voice';
>>>>>>> 3.0

    /**
     * Return the message type.
     *
     * @return string
     */
    public function getMessageType();

    /**
     * Return message content.
     *
<<<<<<< HEAD
     * @param \Overtrue\EasySms\Contracts\GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getContent(GatewayInterface $gateway = null);
=======
     * @return string
     */
    public function getContent(?GatewayInterface $gateway = null);
>>>>>>> 3.0

    /**
     * Return the template id of message.
     *
<<<<<<< HEAD
     * @param \Overtrue\EasySms\Contracts\GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getTemplate(GatewayInterface $gateway = null);
=======
     * @return string
     */
    public function getTemplate(?GatewayInterface $gateway = null);
>>>>>>> 3.0

    /**
     * Return the template data of message.
     *
<<<<<<< HEAD
     * @param \Overtrue\EasySms\Contracts\GatewayInterface|null $gateway
     *
     * @return array
     */
    public function getData(GatewayInterface $gateway = null);
=======
     * @return array
     */
    public function getData(?GatewayInterface $gateway = null);
>>>>>>> 3.0

    /**
     * Return message supported gateways.
     *
     * @return array
     */
    public function getGateways();
}
