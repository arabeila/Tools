<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:reset-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置用户密码';

    protected $model;

    protected $phone;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->model = $user;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        phone:
        $this->phone = $this->ask('请输入手机号');

        if (!$this->checkPhone($this->phone)) {
            $this->error('用户不存在,请重新输入!');
            goto phone;
        }

        password:
        $password = $this->secret('请输入密码');
        $passwordRepeat = $this->secret('请重复密码');

        if (!$this->checkPassword($password, $passwordRepeat)) {
            $this->error('两次密码不一致,请重新输入!');
            goto password;
        }

        if ($this->modifyPassword($password)) {
            $this->info('密码修改成功!');
        } else {
            $this->error('密码修改失败!');
        }
    }

    /**
     * 检查手机号
     * @desc 检查手机号
     * @return mixed
     */
    public function checkPhone()
    {
        return $this->model->where('phone', $this->phone)->exists();
    }

    /**
     * 检查密码
     * @desc 检查密码
     * @param $password
     * @param $passwordRepeat
     * @return bool
     */
    public function checkPassword($password, $passwordRepeat)
    {
        return $password == $passwordRepeat;
    }

    /**
     * 修改密码
     * @desc 修改密码
     * @param $password
     * @return mixed
     */
    public function modifyPassword($password)
    {
        $data = [
            'password' => Hash::make($password),
        ];

        return $this->model->where('phone', $this->phone)->update($data);
    }
}
