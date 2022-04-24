<?php
/**
 * This file is part of G.Snowhawk Application.
 *
 * Copyright (c)2016 PlusFive (https://www.plus-5.com)
 *
 * This software is released under the MIT License.
 * https://www.plus-5.com/licenses/mit-license
 */

namespace Gsnowhawk\Srm\Template;

use Gsnowhawk\Common\Http;
use Gsnowhawk\Common\Lang;

/**
 * Template management request receive class.
 *
 * @license https://www.plus-5.com/licenses/mit-license  MIT License
 * @author  Taka Goto <www.plus-5.com>
 */
class Receive extends Response
{
    /**
     * Save the data.
     */
    public function save()
    {
        if (parent::save()) {
            $this->session->param('messages', Lang::translate('SUCCESS_SAVED'));
            Http::redirect(
                $this->env->server('SCRIPT_NAME').'?mode=srm.template.response'
            );
        }
        $this->view->bind('err', $this->app->err);
        $this->edit();
    }

    /**
     * Remove data.
     */
    public function remove()
    {
        if (parent::remove()) {
            $this->session->param('messages', Lang::translate('SUCCESS_REMOVED'));
        }
        Http::redirect(
            $this->env->server('SCRIPT_NAME').'?mode=srm.template.response'
        );
    }
}
