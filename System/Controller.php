<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/12
 * Time: 0:46
 */
class Controller
{
    public function __construct()
    {
        $this->{App::$CurrentPage};
        $component = $this->{App::$CurrentPage}->Component;
        foreach ($component as $key => $value) {
            $component = array_merge($component, $value->Component);
        }

        $component = array_reverse($component);
        foreach ($component as $value) {
            $value->Start();
        }

        $this->{App::$CurrentPage}->Start();
    }

    public function Show()
    {
        if (func_num_args()) {
            echo App::View(func_get_args()[0]);
        } else {
            echo App::View(App::$CurrentPage);
        }
    }

    /*
     * 用于挂载Page
     */
    public function __get($key)
    {
        if (isset(App::$Page[$key]))
            return App::$Page[$key];
        else
            Errors::Exception("Page $key Existn't!");
    }
}