<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
Encore\Admin\Form::forget(['map']);

//use App\Admin\Extensions\WangEditor;
//use Encore\Admin\Form;
//
//Form::extend('editor', WangEditor::class);
//use App\Admin\Extensions\Form\CKEditor;
//use Encore\Admin\Form;
//
//Form::extend('ckeditor', CKEditor::class);
use App\Admin\Extensions\UEditor;
use Encore\Admin\Form;

Form::extend('editor', UEditor::class);