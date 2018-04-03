<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\CheckExam;
use App\Admin\Extensions\CheckExams;
use App\Admin\Model\Exam;

use App\Admin\Model\Profession;
use App\Admin\Model\Study;
use App\Admin\Model\Video;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) use($request){
            $vid=$request->get('vid');
            if($vid){
                $video=Video::find($vid);
                $content->header('单选题');
                $content->description('视频：'.$video->name);

                $content->body($this->grid($vid));
            }else {
                $content->header('单选题');
                $content->description('试题列表');

                $content->body($this->grid());
            }
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('单选题');
            $content->description('修改试题');

            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('单选题');
            $content->description('新增试题');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($vid=0)
    {
        return Admin::grid(Exam::class, function (Grid $grid) use($vid){
            if($vid)
                $grid->model()->where('vid',$vid);
            $grid->id('ID')->sortable();
            $grid->info('问题')->limit(30);
            $grid->true('正确答案')->limit(30);
            $grid->pid('所属专业')->display(function($pid){
                return Profession::find($pid)->name;
            });
            $grid->sid('所属课程')->display(function($sid){
                return Study::find($sid)->name;
            });
            $grid->vid('所属视频')->display(function($vid){
                $a=Video::find($vid);
                return ($vid&&$a) ? $a->name : '不属于视频问题';
            });
            $grid->status('审核状态')->display(function($status){
                return $status?'已审核':'未审核';
            });
            $grid->created_at('创建时间');
            $grid->updated_at('修改时间');
            $grid->actions(function ($actions) {
                if($actions->row->status==0) {
                    // append一个操作
                    $actions->prepend(new CheckExam($actions->getKey()));
                }
            });
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->add('审核通过', new CheckExams());
                });
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id=0)
    {
        return Admin::form(Exam::class, function (Form $form)use($id) {
            $form->display('id', 'ID');
            $form->text('info', '题目')
                ->rules('required',[
                    'required'=>'请输入题目'
                ]);
            $form->text('choose_1', '选项1')
                ->rules('required|different:choose_2,choose_3,choose_4',[
                    'required'=>'请输入选项1',
                    'different'=>'选项之间不能相同'
                ]);
            $form->text('choose_2', '选项2')
                ->rules('required|different:choose_1,choose_3,choose_4',[
                    'required'=>'请输入选项2',
                    'different'=>'选项之间不能相同'
                ]);
            $form->text('choose_3', '选项3')
                ->rules('required|different:choose_1,choose_2,choose_4',[
                    'required'=>'请输入选项3',
                    'different'=>'选项之间不能相同'
                ]);
            $form->text('choose_4', '选项4')
                ->rules('required|different:choose_1,choose_3,choose_2',[
                    'required'=>'请输入选项4',
                    'different'=>'选项之间不能相同'
                ]);
            if($id){
                $data=Exam::find($id)->toArray();
                $checked='';
                foreach($data as $k=>$v){
                    if($data['true']==$v && $k!='true'){
                        $checked=$k;
                    }
                }
                $form->hidden('true');
                $form->radio('true1', '正确答案')
                    ->rules('required', [
                        'required' => '请选择正确答案'
                    ])
                    ->options([
                        'choose_1' => '选项1',
                        'choose_2' => '选项2',
                        'choose_3' => '选项3',
                        'choose_4' => '选项4'
                    ])->default($checked);
                $form->select('pid', '所属专业')
                    ->options(Profession::pluck('name', 'id'))
                    ->load('sid', '/admin/api/getStudy')
                    ->rules('required', [
                        'required' => '请选择所属专业'
                    ])
                    ->setWidth(2);

                $form->select('sid', '所属课程')
                    ->rules('required', [
                        'required' => '请选择所属课程'
                    ])
                    ->options(Study::where('pid',$data['pid'])->get()->pluck('name','id'))
                    ->load('vid', '/admin/api/getVideo')
                    ->setWidth(2);
                $video=Video::where('sid',$data['sid'])->get()->pluck('name','id')->toArray();
                $video[0]='不属于视频问题';
                $form->select('vid', '所属视频')
                    ->options($video)
                    ->setWidth(2);
            }
            else {
                $form->radio('true', '正确答案')
                    ->rules('required', [
                        'required' => '请选择正确答案'
                    ])
                    ->options([
                        'choose_1' => '选项1',
                        'choose_2' => '选项2',
                        'choose_3' => '选项3',
                        'choose_4' => '选项4'
                    ])->default('choose_1');
                $form->select('pid', '所属专业')
                    ->options(Profession::pluck('name', 'id'))
                    ->load('sid', '/admin/api/getStudy')
                    ->rules('required', [
                        'required' => '请选择所属专业'
                    ])
                    ->setWidth(2);

                $form->select('sid', '所属课程')
                    ->rules('required', [
                        'required' => '请选择所属课程'
                    ])
                    ->load('vid', '/admin/api/getVideo')
                    ->setWidth(2);

                $form->select('vid', '所属视频')->setWidth(2);
            }

            $form->saving(function(Form $form){
                if($form->true1)
                    $form->true=$form->true1;
                $true=$form->true;
                $form->true=$form->$true;
            });

        });
    }
}
