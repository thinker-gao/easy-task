const app = getApp(),
  icon = app.requirejs('icons'),
  c = app.requirejs('core'),
  defferd = app.defferd,
  MODE = 'mode',
  STARDARD_MODE = 'stardard_mode',
  PERSON_MODE = 'person_mode',
  BETTERY = 'bettery';
import { formatNumber, formatDate, colors } from '../../utils/util.js';
let timer = null,
    countnumber = 15,
    totaltime = 0,
    modeSelect = 0,
    stardardIndex = 0,
    tmptime = 0,
    usetime = 0, //记录暂停时间
    datatext = '';
Page({
  data: {
    userinfo: {},
    showstatus: {
      connected: 0,//是否连接
      charged: 0,// 是否获取到电量
    },
    isloading: false,
    skin: 1,//皮肤
    icon: icon,
    colors,
    showModal: '',//弹出框
    paused: false,
    lineRect: {},
    isstart: false,
    isBool: false,
    stardardModeSelected: 0,//默认模式（轻柔）
    modeSelected: 0,//0：标准模式、1：个性模式
    currentIndex: 0,//滑块类型(0：亮度、1：时间)
    lightvalue: 50,//亮度
    timevalue: 50,//时间
    lightdrag: 50,
    timedrag: 50,
    bettery: 0,
    blecurrent:0,
    afterlightvalue: 50,//最后确认灯的亮度值
    totaltime: 15,//默认时间
    usetime: 25,//默认使用时间
    stardardTextMap: ['轻柔', '适中', '强力'],
    today: formatDate(undefined, 'yyyy.MM.dd')
  },
  onLoad: function (options) {
    this.getUserinfo();
    defferd.done(this.getUserinfo);
  },
  onShow: function () {
    var showstatus = new Object();
    var bettery = app.globalData.bettery;

    if (bettery != 0 &&  app.globalData.deviceId==''){
      this.onLoad();
    }
    app.globalData.deviceId != '' ? showstatus.connected = 1 : showstatus.connected = 0
    if (showstatus.connected==0){ this.reset();}
    this.setData({
      skin: app.getCache('skin'),
      bettery: bettery,
      showstatus: showstatus
    });
    this.listenAdapterState();
    this.listenBLEConnectionState();
    this.getbettery_current();
  },
  getbettery_current:function(){
    var self=this;
    setInterval(()=>{
      //电量监控
      var bettery = app.globalData.bettery;
      this.setData({
        bettery: bettery,        
      });
      if (bettery == 20 && app.globalData.isnotice==0){
        app.globalData.isnotice=1
        wx.showModal({
          title: '提示',
          content: '电池电量不足!',
          showCancel:false,
          confirmText:'我知道了',
        })       
      }
      //电流监控
      var blecurrent = app.globalData.blecurrent;
      this.setData({
        blecurrent: blecurrent,
      }); 
      if (blecurrent < 25 && app.globalData.isothernotice == 0 && this.data.isstart) {
          if (blecurrent < 25 && app.globalData.isothernotice == 0) {
            app.globalData.isothernotice = 1
            //模式1,开始状态
            if (modeSelect == 0 && !self.data.paused) {
              self.pause()
            }
            wx.showModal({
              title: '提示',
              content: '请检查面膜是否断开!',
              showCancel: false,
              confirmText: '我知道了',
            })
          }
      }
      else if (blecurrent > 25) {
        app.globalData.isothernotice = 0
      } 

    },5000);
  },

  getUserinfo() {
    const userinfo = app.getCache('userinfo');
    userinfo && this.setData({
      userinfo
    });
  },
  start(e) {
    this.setData({
      isBool: true,
      currentIndex: parseInt(c.pdata(e).type)
    });
  },
  move(e) {
    if (this.data.isBool) {
      let x = e.touches[0].clientX;
      let minX = x - this.data.lineRect.left;
      let obj = {};
      const map = [{
        value: 'lightvalue',
        drag: 'lightdrag'
      }, {
        value: 'timevalue',
        drag: 'timedrag'
      }];
      if (minX >= this.data.lineRect.width - 15) {
        minX = this.data.lineRect.width - 15;
      }
      if (minX < 0) {
        minX = 0;
      }
      obj[map[this.data.currentIndex].value] = parseInt((minX / (this.data.lineRect.width - 15)) * 100);
      obj[map[this.data.currentIndex].drag] = parseInt((minX / (this.data.lineRect.width-8)) * 100);
      obj.timevalue && (obj.totaltime = Math.max(parseInt(30 * obj.timevalue / 100), 1));
      if (this.data.currentIndex==0) {
        obj.lightvalue = Math.max(obj.lightvalue, 1);
      }
      this.setData(obj);
    }
  },
  end(e) {
    this.setData({
      isBool: false
    });
  },
  modeTap() {
    this.showAnyModal(MODE);
  },

  //自定义模式的确定触发
  selectSureTap() {
    this.setData({
      afterlightvalue: this.data.lightvalue
    });
    if (this.data.isstart) {
      var time = modeSelect === 0 ? this.data.usetime * 60 : totaltime * 60 - this.data.usetime * 60;
      //this.addhistory(time, modeSelect, datatext);
    }
    modeSelect = 1;
    this.reset();
    var lightvalue = this.data.lightvalue;
    this.closeAnyModal();
  },
  reset() {
    this.setData({
      isstart: false,
      isused: false,
      paused: false
    });
    usetime = 25;
    clearInterval(timer);
  },
  modeSelect(e) {
    var self = this;
    const type = c.pdata(e).type;
    const index = parseInt(c.data(e).index);
    if (type === 'mode') {
      //选择模式
      if (!isNaN(index)) {

        this.setData({
          modeSelected: index
        });
        
        const mode = index === 0 ? STARDARD_MODE : PERSON_MODE;
        this.showAnyModal(mode);
      }
    } else if (type === 'stardardmode') {
      //标准模式条目选择触发
      if (!isNaN(index)) {
        if (this.data.stardardModeSelected == index && modeSelect === 0) {
          this.closeAnyModal();
          return;
        }
        if (this.data.isstart && this.data.usetime > 0) {
          //console.log(this.data.usetime);
          //self.addhistory(time, modeSelect, datatext);
        }
        this.setData({
          stardardModeSelected: index
        });
        modeSelect = 0;
        this.reset();

        this.closeAnyModal();
      }
    }
  },
  addhistory: function (time, mode, light){
    //console.log('日历记录:时间->'+time+'模式->'+mode+'说明->'+light);
      var self=this;
      c.post('member/history/add', { time: time, mode: mode, light: light},function(data){
      });
  },
  //标准模式方法
  standardmode: function (level,ctype) {
    var self = this;
    var deviceId = app.globalData.deviceId
    level++
    if(ctype==undefined){
      ctype=1;
    }
    else{
      ctype=0;
    }
    //console.log('标准模式执行:' + level + ',' + ctype);   
    //指令构造
    var hex = 'fa010' + level + '0'+ctype+'000c22'
    console.log('本次执行命令:'+hex);
    var typedArray = new Uint8Array(hex.match(/[\da-f]{2}/gi).map(function (h) {
      return parseInt(h, 16)
    }))
    var buffer = typedArray.buffer
    //指令构造
    wx.writeBLECharacteristicValue({
      deviceId: deviceId,
      serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
      characteristicId: '0000FFF1-0000-1000-8000-00805F9B34FB',
      value: buffer,
      success: function (res) {
        //console.log('writeBLECharacteristicValue success', res.errMsg)
      }
    })
  }
  ,
  //个性模式方法
  personalitypattern: function (lightvalue, totaltime) {
   // console.log('个性模式执行:' + lightvalue + ',' + totaltime);   
    var self = this;
    var deviceId = app.globalData.deviceId
    //01.亮度处理    
    lightvalue = lightvalue.toString(16);
    lightvalue.toString().length > 1 ? lightvalue : lightvalue = '0' + lightvalue;
    //02.时间处理
    totaltime *= 60;
    totaltime = totaltime.toString(16);
    console.log(totaltime);
    
    if (totaltime.length == 2){
      totaltime = totaltime + '00'
    }
    else if (totaltime.length == 3){
      totaltime = totaltime[1] + totaltime[2] +'0'+ totaltime[0];
    }
    
    //2位数 
    //totaltime.length == 2 ?  : totaltime;
    //3位数
    //totaltime.length == 3 ? ;
    //时间低位和高位处理
    //totaltime = '' + totaltime[2] + totaltime[3] + totaltime[0] + totaltime[1] + ''
    //console.log(totaltime);    
    //指令构造
    var hex = 'fa02' + lightvalue + totaltime + '0c22'
    console.log('本次执行命令:' + hex);
    var typedArray = new Uint8Array(hex.match(/[\da-f]{2}/gi).map(function (h) {
      return parseInt(h, 16)
    }))
    var buffer = typedArray.buffer
    //指令构造
    wx.writeBLECharacteristicValue({
      deviceId: deviceId,
      serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
      characteristicId: '0000FFF1-0000-1000-8000-00805F9B34FB',
      value: buffer,
      success: function (res) {
        //console.log('writeBLECharacteristicValue success', res.errMsg)
      }
    })
  },
  play() {
    const islogin = app.getCache('islogin');
    if (!islogin) {
      wx.navigateTo({
        url: '/pages/member/login/login'
      });
      return;
    }


    if (app.globalData.deviceId == '') {
      wx.navigateTo({
        url: '/pages/devices/devices'
      });
      return;
    } 

    

    //开始使用
    let time = this.data.modeSelected === 0 ? ((usetime ? usetime : 25)*60) : (this.data.usetime = this.data.totaltime, this.data.usetime * 60);
    totaltime = this.data.totaltime;
    modeSelect = this.data.modeSelected;
    stardardIndex = this.data.stardardModeSelected;
    modeSelect === 0 ? (datatext = this.data.stardardTextMap[stardardIndex]):(datatext = this.data.lightvalue);
    modeSelect === 0 ? this.standardmode(stardardIndex) : this.personalitypattern(datatext, totaltime);
    this.setData({
      isstart: true,
      countdown: this.getFormatTime(time)
    });
    /*if (this.data.modeSelected === 0) {
      timer = setInterval(() => {
        time += 1;
        countup.call(this, time);
      }, 1000);
    } else if (this.data.modeSelected === 1) {*/
      tmptime = this.data.totaltime;
      timer = setInterval(() => {
        time -= 1;
        countdown.call(this, time);
      }, 1000);
    //}

    function countup(time) {
      this.setData({
        countdown: this.getFormatTime(time),
        usetime: time / 60,
        isused: true
      });
    }
    function countdown() {

      if (time >= 0) {   
       
        this.setData({
          countdown: this.getFormatTime(time),
          usetime: time / 60
        });
      } else {
        if (this.data.modeSelected === 1){
          this.addhistory(tmptime, 1, this.data.lightvalue);
        }
        else
        {
          //console.log(datatext = this.data.stardardTextMap[stardardIndex])
          this.addhistory(25, 0, datatext);         
        }
       
        this.setData({
          countdownend: true
        });
        this.reset();
      }
    }
  },
  pause() {
    var self=this;
    //暂停
    if (this.data.modeSelected === 1 && !this.data.countdownend) { c.alert('请等待使用结束'); return; }
    this.setData({
      paused: !this.data.paused
    });

    usetime = this.data.usetime;

    this.data.paused && (clearInterval(timer), this.standardmode(this.data.stardardModeSelected, 0))
    !this.data.paused && this.play();
  },
  getFormatTime(time) {
    let minute = parseInt(time / 60 % 60);
    let second = parseInt(time % 60);
    return formatNumber(minute) + ':' + formatNumber(second)
  },
  showAnyModal(showModal) {
    this.setData({
      showModal
    });
    if (showModal === PERSON_MODE) this.setSliderRect();
  },
  closeAnyModal() {
    this.setData({
      showModal: ''
    });
  },
  setSliderRect() {
    wx.createSelectorQuery().select('#slide').boundingClientRect((lineRect) => {
      this.setData({
        lineRect
      });
    }).exec();
  },
  //监听蓝牙是否正常
  listenAdapterState:function(){
    var self=this;
    wx.onBluetoothAdapterStateChange(function (res) {
      if (!res.available) {
        self.blueclose('蓝牙已关闭');
      }
    })
  },
  //监听Ble设备是否丢失
  listenBLEConnectionState:function(){
    var self = this;
    wx.onBLEConnectionStateChange(function (res) {
      if (!res.connected){
        self.blueclose('设备已断开');
      }
    })   
  },
  //蓝牙适配器关闭或者ble设备断开的公共方法
  blueclose:function (strtext) {
    app.globalData.deviceId = '';
    app.globalData.isnotice =0;
    app.globalData.bettery =0;
    this.reset();
    c.toast(strtext);
    this.onShow();
  },  
  onShareAppMessage() { },
  share () {
    this.onShareAppMessage();
  },
  tourl(e) {
    const url = c.pdata(e).url,
          redirect = c.pdata(e).redirect,
          go = c.pdata(e).go,
          start = this.data.isstart,
          paused = this.data.paused;
    //console.log(start);
    //console.log(paused);
    if (start && !paused && !go) {
      c.alert('请先暂停使用再进行其他操作！');
      return ;
    }
    if (redirect) {
      wx.redirectTo({
        url
      })
    }else {
      wx.navigateTo({
        url
      })
    }
  }
})