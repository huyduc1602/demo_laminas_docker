<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\View\Model\JsonModel;

// Create an action controller.
class HelloController extends AbstractActionController
{
    protected $acceptCriteria = [
        \Laminas\View\Model\ViewModel::class => [
            'text/html',
            'application/xhtml+xml',
        ],
        \Laminas\View\Model\JsonModel::class => [
            'application/json',
            'application/javascript',
        ],
        \Laminas\View\Model\FeedModel::class => [
            'application/rss+xml',
            'application/atom+xml',
        ],
    ];

    public function setPluginManager(PluginManager $plugins)
    {
        $this->plugins = $plugins;
        $this->plugins->setController($this);

        return $this;
    }

    public function getPluginManager()
    {
        if (!$this->plugins) {
            $this->setPluginManager(new PluginManager());
        }

        return $this->plugins;
    }

    public function plugin($name, array $options = null)
    {
        return $this->getPluginManager()->get($name, $options);
    }

    // Define an action "world".
    public function worldAction()
    {
        // $this->layout()->setTemplate('application/hello/test');
        // Get "message" from the query parameters.
        // In production code, it's a good idea to sanitize user input.
        $message = $this->params()->fromQuery('message', 'hello');

        // Pass variables to the view.
        return new ViewModel(['message' => $message]);
    }

    public function apiAction()
    {
        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);

        // Potentially vary execution based on model returned
        if ($viewModel instanceof \Laminas\View\Model\JsonModel) {
            return $this->getResponse()->setContent(['viewModel'=>'JsonModel']);
        }

        $test = $this->params()->fromPost('test', "helloApi");
        $data = [
            "test" => $test,
        ];

        $response = $this->getResponse();
        $jsonModel = new JsonModel($data);
        $response->setContent($jsonModel->serialize());

        return $response;
    }
}