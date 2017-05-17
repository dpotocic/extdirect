<?php

namespace iqria\extdirect;

use yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\Request;

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

    protected $isFormHandler = false;
    protected $isFormUpload = false;
    /**
     * Attach response behavior to controller where the action is.
     *    - raw text in case of getting API
     *    - json when the data need to be processed
     */
    public function init()
    {

        $contentType = Yii::$app->request->getContentType();

        if($contentType == 'application/x-www-form-urlencoded; charset=UTF-8'){
            $this->isFormHandler = true;
        }elseif(substr($contentType, 0, 20) == 'multipart/form-data;'){
            $this->isFormUpload = true;
            $this->requestBody = Yii::$app->request->getRawBody();
        }else{
            $this->requestBody = Json::decode(Yii::$app->request->getRawBody());
        }

        if (!$this->controller->getBehavior('responseFormatter')) {
            $this->controller->attachBehavior('responseFormatter', [
                    'class' => 'yii\filters\ContentNegotiator',
                    'formats' => [
                        !$this->requestBody && !$this->isFormHandler ? Response::FORMAT_RAW : Response::FORMAT_JSON,
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

        if (!$this->requestBody && !$this->isFormHandler && !$this->isFormUpload) {
            return Yii::$app->extDirect->getApi($api);
        } else if ($this->isFormHandler && !$this->isFormUpload) {
            $this->requestBody = [];
            $this->requestBody['tid'] = Yii::$app->request->post('extTID');
            $this->requestBody['action'] = Yii::$app->request->post('extAction');
            $this->requestBody['method'] = Yii::$app->request->post('extMethod');
            $this->requestBody['data'] = [];
            return $this->processRequest($this->requestBody);
        } else if ($this->isFormUpload) {
            $this->requestBody = [];
            $this->requestBody['tid'] = Yii::$app->request->post('extTID');
            $this->requestBody['action'] = Yii::$app->request->post('extAction');
            $this->requestBody['method'] = Yii::$app->request->post('extMethod');
            $this->requestBody['data'] = [];
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