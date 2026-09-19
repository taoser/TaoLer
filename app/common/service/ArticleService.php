<?php

namespace app\common\service;

use Exception;
use app\common\strategy\ArticleValidation;
use app\common\strategy\ValidationStrategy;
use app\common\decorator\ArticleProcessor;
use app\common\decorator\ArticleProcessorDecorator;
use app\common\observer\ObserverManager;
use app\common\observer\Observer;
use app\entity\Article;

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

    /**
     * 添加文章
     * @param array $data 文章数据
     * @return Article 文章实体
     */
    public function add(array $data, Article $article): Article
    {
        // 校验
        if($this->validation) {
            $this->validation->validate($data);
        }

        // 装饰器
        if($this->decorator) {
            $data = $this->decorator->process($data);
        }

        $article->save($data);

        $data['article_id'] = $article->id;
        $data['status']     = $article->status;

        // 通知观察者
        if($this->observer) {
            $this->observer->notify($data);
        }

        return $article;        
    }

    /**
     * 编辑文章
     * @param array $data 文章数据
     * @param Article $article 文章实体
     * @return Article 文章实体
     */
    public function edit(array $data, Article $article): Article
    {
        // 校验
        if($this->validation) {
            $this->validation->validate($data);
        }
        // 装饰器
        if($this->decorator) {
            $data = $this->decorator->process($data);
        }
        // 数据保存
        if(!empty($data['id'])){
            unset($data['id']);
        }

        $article->save($data);

        $data['id'] = $article->id;
        $data['status']     = $article->status;

        // 通知观察者
        if($this->observer) {
            $this->observer->notify($data);
        }

        return $article;
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