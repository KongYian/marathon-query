<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="static/vue-src.js"></script>
    <script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js"></script>
    <script src="https://cdn.bootcss.com/layer/3.0.3/layer.min.js"></script>
    <link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.2/css/amazeui.min.css">
    <title>marathon helper</title>
</head>
<body>
<header data-am-widget="header"
        class="am-header am-header-default">
    <div class="am-header-left am-header-nav">
        <a href="#left-link" class="">
            <i class="am-header-icon am-icon-home" style="margin-top: 15px;"></i>
        </a>
    </div>
    <h1 class="am-header-title">
        <a href="#title-link" class="">
            Marathon Helper
        </a>
    </h1>
    <div class="am-header-right am-header-nav">
        <a href="#right-link" class="">
            <i class="am-header-icon am-icon-bars" style="margin-top: 15px;"></i>
        </a>
    </div>
</header>

<div id="app">
    <div v-show="showSearch" style="margin-top: 5px;">
        <form action="" class="am-form">
            <div class="am-form-group am-form-success am-form-icon am-form-feedback">
                <input type="text" id="doc-ipt-success" class="am-form-field" v-model="name" placeholder="姓名" style="border-radius: 5px" required>
            </div>
            <div class="am-form-group am-form-success am-form-icon am-form-feedback">
                <input type="text" id="doc-ipt-success" class="am-form-field" v-model="idnum" placeholder="身份证号码" style="border-radius: 5px" required>
            </div>
            <div class="am-form-group am-form-success am-form-icon am-form-feedback">
                <input type="text" id="doc-ipt-success" class="am-form-field" v-model="code" placeholder="验证码" style="border-radius: 5px" required>
            </div>
        </form>
        <div style="text-align: center">
            <img v-bind:src = 'imageSrc'>
        </div>
        <button type="button" class="am-btn am-btn-secondary" @click="query" style="border-radius: 5px;margin-left: 5px;">查询</button>
    </div>
    <div v-show="!showSearch" style="margin-top: 5px;margin-left: 5px;">
        <button type="button" class="am-btn am-btn-secondary" @click="reload" style="border-radius: 5px;margin-left: 5px;" >返回查询</button>
    </div>
    <div v-show="result">
        <table class="am-table">
            <thead>
            <tr>
                <th>日期</th>
                <th>比赛名称</th>
                <th>类型</th>
                <th>净成绩</th>
                <th>枪响成绩</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="res in result">
                <td v-text="res.date"></td>
                <td v-text="res.name"></td>
                <td v-text="res.type"></td>
                <td :style="{color:res.pbColor}" v-text="res.raceNetTime"></td>
                <td v-text="res.raceTrueTime"></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<script>
    var vm = new Vue({
        el:'#app',
        data:{
            name:localStorage.getItem('name')=='undefined'?'':localStorage.getItem('name'),
            idnum:localStorage.getItem('idnum')=='undefined'?'':localStorage.getItem('idnum'),
            code:'',
            showSearch : 1,
            result:'',
            imageSrc : 'image/verifyCode.jpg',
            isPBColor :'pink'
        },
        beforeCreate:function(){
            cookie = init();
        },
        filters:{

        },
        methods:{
            query:function () {
                if(!(this.name && this.idnum && this.code && cookie)){
                    layer.msg('每一项都要填写:)');
                    return false;
                }
                var load = layer.load();
                $.ajax({
                    url:'action/search.php',
                    data:{
                        name:this.name,
                        idnum:this.idnum,
                        code:this.code,
                        cookie:cookie,
                    },
                    dataType:'json',
                    type:'post',
                    success:function (response) {
                        if(response.status == 1){
                            vm.result = response.data;
                            vm.showSearch = 0;
                            localStorage.setItem('name',vm.name);
                            localStorage.setItem('idnum',vm.idnum);
                        }else{
                            layer.msg('未查询到成绩,再试试吧QAQ');
                            vm.reload();
                            return false;
                        }
                    },
                    error:function () {
                        layer.msg('服务器开小差啦,稍后再试');
                    },
                    complete:function () {
                        layer.close(load)
                    }
                })
            },
            reload:function () {
                window.location.reload();
            }
        }
    })

    function init() {
        var  cookieString;
        $.ajax({
            url:'action/init.php',
            dataType:'json',
            type:'post',
            async:false,
            success:function (response) {
                cookieString = response.data
            },
            error:function () {

            }
        })
        return cookieString;
    }
</script>

<style>
    .pbColor:{
        color: #2ea6f8;
    }
</style>