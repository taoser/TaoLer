<?php

namespace think\dumper;

use Symfony\Component\VarDumper\Cloner\Data;

class Connection
{
    private string $host;
    private string $token;
    private array  $contextProviders;

    private array $handles = [];

    public function __construct(array $contextProviders = [])
    {
        $this->host             = env('DUMPER_HOST', 'https://developer.topthink.com');
        $this->token            = env('DUMPER_TOKEN');
        $this->contextProviders = $contextProviders;
    }

    public function write(Data $data): bool
    {
        if (!$handle = $this->getHandle() ?: $this->createHandle()) {
            return false;
        }
        $this->setHandle($handle);

        $context = ['timestamp' => microtime(true)];
        foreach ($this->contextProviders as $name => $provider) {
            $context[$name] = $provider->getContext();
        }
        $context        = array_filter($context);
        $encodedPayload = base64_encode(serialize([$data, $context])) . "\n";

        set_error_handler(fn() => true);
        try {
            return $this->sendPayload($encodedPayload);
        } finally {
            restore_error_handler();
        }
    }

    private function getCoroutineId(): string
    {
        if (extension_loaded('swoole') && $cid = \Swoole\Coroutine::getCid()) {
            return (string) $cid;
        }
        return 'default';
    }

    private function getHandle()
    {
        $cid = $this->getCoroutineId();
        return $this->handles[$cid] ?? null;
    }

    private function setHandle($handle): void
    {
        $cid = $this->getCoroutineId();
        if ($handle === null) {
            unset($this->handles[$cid]);
        } else {
            $this->handles[$cid] = $handle;
        }
    }

    private function sendPayload($payload)
    {
        $handle = $this->getHandle();
        curl_setopt($handle, CURLOPT_POSTFIELDS, $payload);
        $res = curl_exec($handle);
        if ($res === false) {
            curl_close($handle);
            $this->setHandle(null);
            return false;
        }
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        return $code == 204;
    }

    private function createHandle()
    {
        set_error_handler(fn() => true);
        try {
            $handle = curl_init("{$this->host}/api/thinkphp/dumper/server");
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_HTTPHEADER, [
                "Accept: application/json",
                "Authorization: Bearer {$this->token}",
            ]);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($handle, CURLOPT_TIMEOUT, 3);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 1);
            return $handle;
        } finally {
            restore_error_handler();
        }
    }
}
