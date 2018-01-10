const app = getApp(),
c = app.requirejs('core'),
icon = app.requirejs('icons');
let isloading = false;
import { hexCharCodeToStr, ab2hex, getAuthenticationData } from '../../utils/util.js';
Page({
  data: {
    list:[],//保存设备列表中的信息
    showstatus:{
      text:'',
      status:0
    },
    pullicons: [icon.pull_icon, icon.loading, icon.success],
    pulltexts: ['下拉刷新', '刷新中...', '刷新成功'],
    isrefresh: 0,
    ishidden: true,
    connecting: false,
    ishow:0,
    systemos:0,//0是安卓,1是苹果
  },
  onLoad: function (options) {
  
  },
  onShow: function () {
    var self=this;
    wx.getSystemInfo({
      success: function (res) {
        //console.log(res.system)
        if (res.system.indexOf('iOS') != '-1') {
          self.setData({
            systemos: 1,
          });
        }
        else {
          self.setData({
            systemos: 0,
          });
        }
      }
    })

    this.setData({ ishow: app.globalData.ishow});
    this.open();
  },
  //开启蓝牙
  open: function () {
    var self = this;
    wx.openBluetoothAdapter({
      success: function (res) {
        //开启成功进行自检
        self.init();
      },
      fail: function (res) {
        //console.log(res);
        //开启失败进行后台监听,提示用户开启蓝牙
        wx.getSystemInfo({
          success: function (res) {
            //console.log(res.system)
            if (res.system.indexOf('iOS')!='-1'){
              self.setData({
                showstatus: { text: '请开启蓝牙!苹果系统APi限制,如已开启,请尝试重新开启', status: 1 }
              });
            }
            else
            {
              self.setData({
                showstatus: { text: '请您先开启蓝牙喔!', status: 1 }
              });
            }
          }
        })
        self.listen();
      }
    })

    //001.监听蓝牙适配器状态
    wx.onBluetoothAdapterStateChange(function (res) {
     //console.log('用户可能已经开启蓝牙');
      if (res.available) {
        self.init();
        self.setData({
          showstatus: { text: '请您先开启蓝牙喔!', status: 0 }
        });       
      }
      else {
          //console.log('蓝牙已经关闭');
          self.blueclose('蓝牙已关闭');
      }
    })    
  }
  ,
  //全局初始化
  init: function () {
    var self=this;
    //002.监听发现新的蓝牙设备
    wx.onBluetoothDeviceFound(function (devices) {
      //console.log('new device list has founded')
      //console.dir(devices)
     // console.log((app.ab2hex(devices.devices[0].advertisData)))
    })
    //003.监听处理Ble设备意外断开
    wx.onBLEConnectionStateChange(function (res) {
      if (!!res.connected){
        //console.log('设备已经关闭');
      }
    })

    //004.开始搜索蓝牙
    this.search();

  }  
  ,
  //轮训监听蓝牙适配器状态,开启蓝牙或者关闭蓝牙都能自动触发。
  listen: function () {
    var self = this;
    //console.log('启动后台监听');
    wx.onBluetoothAdapterStateChange(function (res) {
      if (res.available) {
          //console.log('用户开启蓝牙->开始init');
          self.init();
      }
      else{
          //用户突然关闭蓝牙
          self.setData({list:[]});
      }
    })   
  }
  ,
  //开启搜索蓝牙设备功能。一般手机开启蓝牙默认是搜索模式
  search: function () {
    var self = this;
    isloading = true;
    this.setData({ isrefresh: 1 });
    wx.startBluetoothDevicesDiscovery({
      allowDuplicatesKey: false,
      complete: function (res) {
        self.getDevices();
        setTimeout(function () {
          self.setData({
            isrefresh: 2
          });
          setTimeout(() => {
            self.setData({
              isrefresh: 0,
              ishidden: true
            });
            wx.stopPullDownRefresh();
            isloading = false;
          }, 1500);
        }, 1000);
      }
    })
  }
  , 
  //获取在小程序蓝牙模块生效期间所有已发现的蓝牙设备
  getDevices: function () {
    var self = this;
    wx.getBluetoothDevices({
      success: function (res) {
        
        for (var item in res.devices){
          if (ab2hex(res.devices[item].advertisData).length>32){
            var macstring = ab2hex(res.devices[item].advertisData).substr(32, 12);
            macstring = macstring[0] + macstring[1] + ':' + macstring[2] + macstring[3] + ':' + macstring[4] + macstring[5] + ':' + macstring[6] + macstring[7] + ':' + macstring[8] + macstring[9] + ':' + macstring[10] + macstring[11];
          }
          res.devices[item].othermac = macstring;
          if (res.devices[item].deviceId == app.globalData.deviceId){
              res.devices[item].connect=1;
          }
          else
          {
            res.devices[item].connect = 0;
          }
        }
        //console.log(res)
        self.setData({
          list: res.devices
        });
      }
    })      
  }, 
  //连接|断开设备
  connection: function (options) {
    var self = this;
    var list = self.data.list;
    var dataindex = options.target.dataset.index;
    var connect = list[dataindex].connect;    
    var deviceId = list[dataindex].deviceId;

    this.setData({
      connecting: true
    });
    
    if (self.data.ishow==0){
      connect=0;
    }

    //断开设备
    if (connect==1){
      wx.closeBLEConnection({
        deviceId: deviceId,
        success:function(){
          setTimeout(function () {
            c.toast('断开连接');
          }, 1500);   
          app.globalData.deviceId='';        
          list[dataindex].connect=0;
          self.setData({list:list});         
        },
        complete () {
          self.setData({
            connecting: false
          });
        }
      })       
      return;
    }

    self.setData({
      connecting: true
    });
    wx.createBLEConnection({
      deviceId: deviceId,
      success: function (res) {
        //全局记录设备Id.Ble设置是Mac地址
        app.globalData.deviceId = deviceId;
        //连接成功关闭搜索
        wx.stopBluetoothDevicesDiscovery();
        //获取设备的所有服务
        wx.getBLEDeviceServices({
          deviceId: deviceId,
          success: function (res) {
            self.setData({
              service: res.services
            });
            //如果包含本款设置的主服务UUID，记录设备的所有主服务
            //console.log(res.services);
            app.globalData.service = res.services;
            for (var item in res.services ){
              if (res.services[item].uuid =='0000FFF0-0000-1000-8000-00805F9B34FB'){
                  app.globalData.service = res.services;             
                }
            }
            if (app.globalData.service.length<=0){
              self.setData({
                showstatus: { text: '非Led面膜设备!', status: 1 }
              });    
              wx.closeBLEConnection({
                deviceId: deviceId
              })          
              return;                  
            }
            self.setData({
              connecting: true
            });
            //再通过服务查看特征值
            wx.getBLEDeviceCharacteristics({
              deviceId: deviceId,
              serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
              success: function (res) {
                //console.log('获取特征值成功');
                //监听特征值变化.并计算验证码
                wx.onBLECharacteristicValueChange(function (characteristic) {
                  //计算验证码
                  if (self.data.systemos==1){
                    var macstring = list[dataindex].othermac;
                  }
                  else{
                    var macstring = deviceId;
                  }
                 // console.log(macstring);
                  var randstring = ab2hex(characteristic.value);
                  var verifycode = getAuthenticationData(macstring, randstring);
                  //发送验证码
                  self.sendverify(verifycode);
                })
                wx.readBLECharacteristicValue({
                  deviceId: deviceId,
                  serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
                  characteristicId: '0000FFF3-0000-1000-8000-00805F9B34FB',
                  success: function (res) {
                    //console.log('读取:', res.errCode)
                  }
                })
                //监听特征值变化.并计算验证码
              },
              complete () {
                self.setData({
                  connecting: false
                });
              }
            })


          },
          fail: function (res) {
            //console.log('获取设备的服务失败')
          },
          complete() {
            self.setData({
              connecting: false
            });
          }
        })
      },
      fail: function (res) {
        //console.log('no1');
        self.setData({
          ishow:0,
          showstatus: { text: '连接失败,请尝试重新连接!', status: 1 }
        });
      },
      complete() {
        self.setData({
          connecting: false
        });
      }
    })
  }
  ,
  //发送验证码。
  sendverify: function (verifycode) {
    var self = this;
    var deviceId =app.globalData.deviceId;
    //文档建议发送3次
    for (var i = 0; i < 3; i++) {
      var hex =verifycode[0] + verifycode[1];
      var typedArray = new Uint8Array(hex.match(/[\da-f]{2}/gi).map(function (h) {
        return parseInt(h, 16)
      }))
      //console.log('本次执行命令:' + hex);
      var buffer = typedArray.buffer
      wx.writeBLECharacteristicValue({
        deviceId: deviceId,
        serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
        characteristicId: '0000FFF3-0000-1000-8000-00805F9B34FB',
        value: buffer,
        success: function (res) {
          //console.log('writeBLECharacteristicValue success', res.errMsg)
        }
      })
    }

    //文档建议500-1000MS后读取验证码
    setTimeout(function () {
      wx.onBLECharacteristicValueChange(function (characteristic) {
        var rescode = parseInt(ab2hex(characteristic.value),10);
        if (rescode ==1) {
          //console.log('通过验证21');
          wx.notifyBLECharacteristicValueChange({
            state: true, 
            deviceId: deviceId,
            serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
            characteristicId: '0000FFF4-0000-1000-8000-00805F9B34FB',
            success: function (res) {
                //读取电量信息
                wx.onBLECharacteristicValueChange(function (res) {
                  var charge = ab2hex(res.value);
                  console.log('原始数据:'+charge);
                  app.globalData.bettery=parseInt('0x' + charge[8] + charge[9], 16);
                  app.globalData.blecurrent = parseInt('' + charge[4] + charge[5] + charge[6] + charge[7]+'', 16)
                  var dianya = parseInt('' + charge[0] + charge[1] + charge[2] + charge[3] + '', 16)
                  console.log('电压:' + dianya);
                  console.log('电量:' + app.globalData.bettery);
                  console.log('电流:' + app.globalData.blecurrent);                  
                })
              //读取电量信息
            }
          }) 
          app.globalData.ishow = 1;
          c.toast('连接成功');
          self.setData({ ishow: 1 })
          setTimeout(function () {
            wx.navigateBack({
            })
            self.search();
          }, 0);    
          return;                  
        }
        else {
          //验证码验证失败
          //console.log('no21');
          self.setData({
            ishow:0,
            showstatus: { text: 'Err,请尝试重新连接!', status: 1 }
          }); 
          wx.closeBLEConnection({
            deviceId: deviceId
          })                            
        }
      })
      wx.readBLECharacteristicValue({
        deviceId: deviceId,
        serviceId: '0000FFF0-0000-1000-8000-00805F9B34FB',
        characteristicId: '0000FFF3-0000-1000-8000-00805F9B34FB',
        success: function (res) {
        }
      })

    }, 1000)

  },
  //下拉刷新
  onPullDownRefresh() {
    wx.stopPullDownRefresh();
    if (isloading) return;
    this.setData({
      ishidden: false
    })
    this.search();
  },
  //关闭modal
  closeModal:function(){
    this.setData({
      showstatus: { text: '', status: 0 }
    });
  }
  ,
  //蓝牙适配器关闭或者ble设备断开的公共方法
  blueclose:function(strtext){
    app.globalData.deviceId = ''; 
    this.setData({ list: [] });
    c.toast(strtext);
  }
})