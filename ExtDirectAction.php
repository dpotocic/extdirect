<?php

namespace iqria\extdirect;

use yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Class ExtDirectAction returns server's API and handles the request
 *
 * @author Stanislav Hudkov <stanislav.hudkov@iqria.com>
 * @version 1.0
 * @package iqria\extdirect
 */
class ExtDirectAction extends Action
{
    protected $requestBody;

    protected $isPost = false;
    /**
     * Attach response behavior to controller where the action is.
     *    - raw text in case of getting API
     *    - json when the data need to be processed
     */
    public function init()
    {

        $this->isPost = Yii::$app->request->isPost;

        if(!$this->isPost){
            $this->requestBody = Json::decode(Yii::$app->request->getRawBody());
        }

        if (!$this->controller->getBehavior('responseFormatter')) {
            $this->controller->attachBehavior('responseFormatter', [
                    'class' => 'yii\filters\ContentNegotiator',
                    'formats' => [
                        !$this->requestBody && !$this->isPost ? Response::FORMAT_RAW : Response::FORMAT_JSON,
                    ]
                ]);
        }

        parent::init();
    }

    /**
     * @param string $api js|json are allowed parameters
     * @see ExtDirectManager API constants
     * @return mixed
     */
    public function run($api = null)
    {
        if (!$this->requestBody && !$this->isPost) {
            return Yii::$app->extDirect->getApi($api);
        } else if ($this->isPost) {
            $this->requestBody = [];
            $this->requestBody['tid'] = Yii::$app->request->post('extTID');
            $this->requestBody['action'] = Yii::$app->request->post('extAction');
            $this->requestBody['method'] = Yii::$app->request->post('extMethod');
            return $this->processRequest($this->requestBody);
        } else {
            return $this->processRequest($this->requestBody);
        }
    }

    protected function processRequest($body)
    {
        return Yii::$app->extDirect->processRequest($body);
    }
}