const app = getApp(),
  icon = app.requirejs('icons'),
  c = app.requirejs('core');
Page({
  data: {
    icon: icon,
    skins: [
      {
        image: icon.skin_index1,
        text: '幽魅紫（默认）'
      },
      {
        image: icon.skin_index2,
        text: '梦幻时光'
      },
      {
        image: icon.skin_index3,
        text: '流年芳华'
      },
      {
        image: icon.skin_index4,
        text: '简约红调'
      },
      {
        image: icon.skin_index5,
        text: '斑驳陆离'
      },
      {
        image: icon.skin_index6,
        text: '光影交错'
      }
    ]
  },
  onLoad(options) {
  },
  onShow() {
    this.setData({
      selected: app.getCache('skin')-1
    });
  },
  selectTap(e) {
    const selected = parseInt(c.data(e).index);
    this.setData({ selected });
    app.setSkin(selected+1);
    wx.redirectTo({
      url: '/pages/index/index'
    })
  }
})