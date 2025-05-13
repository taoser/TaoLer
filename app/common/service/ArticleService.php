<?php

namespace app\common\service;

use Exception;
use app\common\strategy\ArticleValidation;
use app\common\strategy\ValidationStrategy;
use app\common\decorator\ArticleProcessor;
use app\common\decorator\ArticleProcessorDecorator;
use app\common\observer\ObserverManager;
use app\common\observer\Observer;
use app\facade\Article;

class ArticleService
{
    // 策略校验器
    private $validation = null;

    // 内容装饰器
    private $decorator = null;

    // 观察者管理器
    private $observer = null;

    // public function __construct($observer) {
        
    // }

    public function add($data)
    {
        try{

            // 校验
            if($this->validation) {
                $this->validation->validate($data);
            }

            if($this->decorator) {
                $data = $this->decorator->process($data);
            }

            $article = Article::add($data);
            $data['article_id'] = $article['id'];

            // 通知观察者
            if($this->observer) {
                $this->observer->notify($data);
            }

            return true;
        } catch(Exception $e) {
            // echo "文章发布失败：". $e->getMessage(). "\n";
            // return false;

            throw new Exception($e->getMessage());
        }
        
    }

    // 校验器
    public function setValidation(ArticleValidation $validation) {
        $this->validation = $validation;
        return $this;
    }

    // 设置校验器类
    public function setDecorator(ArticleProcessorDecorator $decorator) {
        $this->decorator = $decorator;
        return $this;
    }

    // 被观察者
    public function setObserverManager(ObserverManager $observer)
    {
        $this->observer = $observer;
        return $this;
    }

    // 添加校验器
    public function addValidation(ValidationStrategy $validation) {
        if ($this->validation) {
            $this->validation->addValidation($validation);
        }
        return $this;
    }

    // 添加校验器
    public function addProcessor(ArticleProcessor $processor) {
        if ($this->decorator) {
            $this->decorator->addProcessor($processor);
        }
        return $this;
    }

    // 被观察者
    public function addObserver(Observer $observer)
    {
        if($this->observer) {
            $this->observer->addObserver($observer);
        }
        return $this;
    }
}