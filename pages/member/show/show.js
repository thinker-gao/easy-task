const app = getApp(),
      c = app.requirejs('core');
Page({
  data: {
    info: {
      realname: '',
      gender: 0,
      birthday: '未设置',
      mobile: ''
    },
    isloading: false
  },
  onLoad: function (options) {
  },
  onShow: function () {
    //获取个人信息
    this.getInfo();
  },
  dateChange(e) {
    let birthday = e.detail.value.split('-').join('.');
    this.data.info.birthday = birthday;
    this.setData({
      info: this.data.info
    });
  },
  save(e) {
    var self=this;
    this.setData({
      isloading: true
    });
    var gender = self.data.info.gender
    var birthday = self.data.info.birthday == '未设置' ? '' : self.data.info.birthday;
    c.post('member/info/submit', { gender: gender, birthday: birthday},function(data){
        self.setData({
          isloading: false
        })
        if(data.status==1){
          c.toast('保存成功!');
          setTimeout(() => {
            wx.navigateBack();
          }, 1500);          
        }
        else
        {
          c.alert(data.result.message);
        }
    });
  },
  getInfo() {
    var self=this;
    this.setData({
      isloading: true
    });
    c.post('member/info',{},function(data){
      self.setData({
        isloading: false
      }) 
      if (data.error==0){
          var info={
            realname: data.member.realname == '' ? '未设置' : data.member.realname,
            gender: data.member.gender,
            mobile: data.member.mobile,
            birthday: data.member.birthday == '' ? '未设置' : data.member.birthday

          }
          self.setData({ info:info});      
      }
      else
      {
        c.alert('获取用户信息失败,请重试!');
      }
    });
  }
  ,
  upgender(options){
    var gender = options.target.dataset.id
    var info=this.data.info;
    info.gender = gender;
    this.setData({
      info: info
    });
  }
})