<?php

namespace Nogo\Api\View;

/**
 * Json view encode array as json.
 *
 * @author Danilo Kuehn <dk@nogo-software.de>
 */
class Json extends \Slim\View
{
    protected function render($template, array $data = null)
    {
        if ($this->data->has('flash')) {
            $this->data->remove('flash');
        }
        $data = array_merge($this->data->all(), (array) $data);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
