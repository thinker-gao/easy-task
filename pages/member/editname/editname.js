const app = getApp(),
      c = app.requirejs('core');
import {trim} from '../../../utils/util.js';
Page({
  data: {
    name: '',
    isloading: false
  },
  onLoad: function (options) {
    console.log(options);
    if (options.realname != undefined && options.realname != ''){
      this.setData({ name: options.realname});
    }
  },
  onShow: function (options) {

  },
  submit(e) {
    var self=this;
    const name = e.detail.value.name;
    if (trim(name) === '') {
      c.alert('请输入您的姓名');
      return;
    }
    console.log(name);
    //执行异步操作（暂时模拟）
    this.setData({
      isloading: true
    });
    c.post('member/info/realname', { realname: name},function(data){
        self.setData({
          isloading: false
        });
        if(data.status==1){
          c.toast('修改成功!');
          setTimeout(() => {
            wx.navigateBack()
          }, 1500)
        }
        else
        {
          c.alert(data.result.message);
        }
    });

  }
})