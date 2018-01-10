const app = getApp(),
      c = app.requirejs('core');
let choose_year = null,
  choose_month = null;
Page({
  data: {
    hasEmptyGrid: false,
    showPicker: false,
    //模式0代表标准、1代表个性
    data: [],
    textMap: ['标准', '个性'],
    list: [],
    currentday: new Date().getDate()
  },
  onLoad() {
  },
  onShow() {
    const date = new Date();
    const cur_year = date.getFullYear();
    const cur_month = date.getMonth() + 1;
    const weeks_ch = ['日', '一', '二', '三', '四', '五', '六'];
    const findday = cur_year + '-' + cur_month + '-' +date.getDate();
    this.setData({
      cur_year,
      cur_month,
      weeks_ch
    });
    this.init(cur_year, cur_month);
    this.getList(findday);
    this.calculateEmptyGrids(cur_year, cur_month);
  },
  //初始化渲染已经使用的日期
  init:function(year,month){
    var  self=this;
    var date = year + '-' + month;
    this.setData({
      isloading: true
    });
    c.post('member/history', { date, date},function(data){
      self.setData({
        isloading: false
      });
      if(data.status==1){
        self.setData({
          data:data.result
        });
      }
      self.calculateDays(year, month);
    })
  },
  // 计算每月有多少天
  getThisMonthDays(year, month) {
    return new Date(year, month, 0).getDate();
  },
  // 计算每月第一天是星期几
  getFirstDayOfWeek(year, month) {
    return new Date(Date.UTC(year, month - 1, 1)).getDay();
  },
  // 计算在每月第一天在当月第一周之前的空余的天数
  calculateEmptyGrids(year, month) {
    const firstDayOfWeek = this.getFirstDayOfWeek(year, month);
    let empytGrids = [];
    if (firstDayOfWeek > 0) {
      for (let i = 0; i < firstDayOfWeek; i++) {
        empytGrids.push(i);
      }
      this.setData({
        hasEmptyGrid: true,
        empytGrids
      });
    } else {
      this.setData({
        hasEmptyGrid: false,
        empytGrids: []
      });
    }
  },
  // 渲染日历格子
  calculateDays(year, month) {
    let days = [];
    const currentmonth = this.data.cur_month;
    const currentyear = this.data.cur_year;
    const todaymonth = new Date().getMonth() + 1;
    const todayyear = new Date().getFullYear();
    const thisMonthDays = this.getThisMonthDays(year, month);
    const data = this.data.data;
    for (let i = 1; i <= thisMonthDays; i++) {
      let used = false;
      data.forEach((item) => {
        const useday = item.day;
        const usemonth = item.month;
        const useyear = item.year;
        if (useday == i && usemonth == currentmonth && useyear == currentyear) {
          used = true;
        }
      });
      days.push({
        day: i,
        choosed: this.data.currentday === i && todaymonth === month && todayyear === year,
        used
      });
    }

    //console.log(days);

    this.setData({
      days
    });
  },
  // 递增、递减切换月份
  handleCalendar(e) {
    const handle = e.currentTarget.dataset.handle;
    const cur_year = this.data.cur_year;
    const cur_month = this.data.cur_month;
    if (handle === 'prev') {
      let newMonth = cur_month - 1;
      let newYear = cur_year;
      if (newMonth < 1) {
        newYear = cur_year - 1;
        newMonth = 12;
      }

      this.init(newYear, newMonth);
      this.calculateEmptyGrids(newYear, newMonth);

      this.setData({
        cur_year: newYear,
        cur_month: newMonth
      });

    } else {
      let newMonth = cur_month + 1;
      let newYear = cur_year;
      if (newMonth > 12) {
        newYear = cur_year + 1;
        newMonth = 1;
      }

      this.init(newYear, newMonth);
      this.calculateEmptyGrids(newYear, newMonth);

      this.setData({
        cur_year: newYear,
        cur_month: newMonth
      });
    }
  },
  getList(findday) {
    c.post('member/history/getlist', {
      findday
    }, (res) => {
      this.setData({
        isloading: false
      });
      if (res.status === 1) {
        this.setData({
          list: res.result
        });
      } else {
        this.setData({
          list: []
        });
      }
    });
  },
  dayItemTap(e) {
    //发送请求
    this.setData({
      isloading: true
    });
    const currentday = c.pdata(e).idx+1;
    console.log(this.data.cur_year + '---' + this.data.cur_month + '----' + c.pdata(e).idx);
    const days = this.data.days;
    for (let i = 0; i < days.length; i++) {
      days[i].choosed = false;
    }
    this.data.days[currentday-1].choosed = true;
    this.setData({
      days
    });
    const findday = this.data.cur_year+'-'+this.data.cur_month+'-'+currentday;
    this.getList(findday);
  }
});