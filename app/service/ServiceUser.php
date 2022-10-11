<?php

namespace app\service;

use app\model\User;
use SimonJWTToken\JWTToken;

class ServiceUser extends Base
{
    public function logIn($d=[]){
        $d = glwb($d);
        if(empty($d['username'])){
            return r_fail('Please enter username!');
        }
        if(empty($d['password'])){
            return r_fail('Please enter password!');
        }

        $d['username'] = filter_spaces($d['username']);
        $d['password'] = filter_spaces($d['password']);
        if(strlen($d['username']) < 6){
            return r_fail('username must be at least 6 characters!');
        }
        if(strlen($d['password']) < 6){
            return r_fail('password must be at least 6 characters!');
        }

        $user = User::where('username',$d['username'])->findOrEmpty()->toArray();
        if(empty($user)){
            return r_fail('username not find!');
        }
        if(md5($d['password']) !== $user['password']){
            return r_fail('password error!');
        }

        $new_JWTToken = new JWTToken();
        $jwtToken = $new_JWTToken->createToken($user['id']);
        if($jwtToken === false){
            return r_fail($new_JWTToken->getErrorMsg());
        }

        return r_ok('ok', ['user'=>$user,'token'=>$jwtToken]);
    }

    public function signUp($d=[]){
        $d = glwb($d);
        if(empty($d['username'])){
            return r_fail('Please enter username!');
        }
        if(empty($d['password'])){
            return r_fail('Please enter password!');
        }

        $d['username'] = filter_spaces($d['username']);
        $d['password'] = filter_spaces($d['password']);
        if(strlen($d['username']) < 6){
            return r_fail('username must be at least 6 characters!');
        }
        if(strlen($d['password']) < 6){
            return r_fail('password must be at least 6 characters!');
        }

        $user = User::where('username',$d['username'])->findOrEmpty()->toArray();
        if(!empty($user)){
            return r_fail('username has been registered!');
        }
        $d['password'] = md5($d['password']);
        $user = User::create($d,['username','password'])->toArray();
        if(empty($user)){
            return r_fail('Sign up has failed!');
        }

        $new_JWTToken = new JWTToken();
        $jwtToken = $new_JWTToken->createToken($user['id']);
        if($jwtToken === false){
            return r_fail($new_JWTToken->getErrorMsg());
        }

        return r_ok('ok', ['user'=>$user,'token'=>$jwtToken]);
    }



}