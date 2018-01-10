function formatTime(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()

  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}

/**
		 * 将日期格式化成指定格式的字符串
		 * @param date 要格式化的日期，不传时默认当前时间，也可以是一个时间戳
		 * @param fmt 目标字符串格式，支持的字符有：y,M,d,q,w,H,h,m,S，默认：yyyy-MM-dd HH:mm:ss
		 * @returns 返回格式化后的日期字符串
		 */
function formatDate(date, fmt) {
  date = date == undefined ? new Date() : date;
  date = typeof date == 'number' ? new Date(date) : date;
  fmt = fmt || 'yyyy-MM-dd HH:mm:ss';
  var obj = {
    'y': date.getFullYear(), // 年份，注意必须用getFullYear
    'M': date.getMonth() + 1, // 月份，注意是从0-11
    'd': date.getDate(), // 日期
    'q': Math.floor((date.getMonth() + 3) / 3), // 季度
    'w': date.getDay(), // 星期，注意是0-6
    'H': date.getHours(), // 24小时制
    'h': date.getHours() % 12 == 0 ? 12 : date.getHours() % 12, // 12小时制
    'm': date.getMinutes(), // 分钟
    's': date.getSeconds(), // 秒
    'S': date.getMilliseconds() // 毫秒
  };
  var week = ['天', '一', '二', '三', '四', '五', '六'];
  for (var i in obj) {
    fmt = fmt.replace(new RegExp(i + '+', 'g'), function (m) {
      var val = obj[i] + '';
      if (i == 'w') return (m.length > 2 ? '星期' : '周') + week[val];
      for (var j = 0, len = val.length; j < m.length - len; j++) val = '0' + val;
      return m.length == 1 ? val : val.substring(val.length - m.length);
    });
  }
  return fmt;
}
//16进制转字符串
function hexCharCodeToStr(hexCharCodeStr) {
  var trimedStr = hexCharCodeStr.trim();
  var rawStr =
    trimedStr.substr(0, 2).toLowerCase() === "0x"
      ?
      trimedStr.substr(2)
      :
      trimedStr;
  var len = rawStr.length;
  if (len % 2 !== 0) {
    alert("Illegal Format ASCII Code!");
    return "";
  }
  var curCharCode;
  var resultStr = [];
  for (var i = 0; i < len; i = i + 2) {
    curCharCode = parseInt(rawStr.substr(i, 2), 16); // ASCII Code Value
    resultStr.push(String.fromCharCode(curCharCode));
  }
  return resultStr.join("");
}
//buffer转16进制
function  ab2hex(buffer) {
  var hexArr = Array.prototype.map.call(
    new Uint8Array(buffer),
    function (bit) {
      return ('00' + bit.toString(16)).slice(-2)
    }
  )
  return hexArr.join('');
}
//共享的验证码算法
function getAuthenticationData(macstring, randstring) {
  var auth_data = new Array();
  //Mac
  var Tempmac = macstring.split(':');
  for (var i = 0; i < Tempmac.length; i++) {
    Tempmac[i] = '0x' + Tempmac[i];
  }
  var mac = new Uint8Array(Tempmac);
  //Mac-End
  //Rand
  var Temprand = new Array();
  var temprandj = 0;
  for (var i = 0; i < randstring.length; i++) {
    Temprand[temprandj] = '0x' + randstring.slice(i, i + 2);
    i++;
    temprandj++;
  }
  var rand = new Uint8Array(Temprand);
  //Rand-End
  auth_data[0] = mac[0] ^ mac[1] ^ mac[2] ^ rand[0] ^ rand[1];
  auth_data[1] = mac[3] ^ mac[4] ^ mac[5] ^ rand[2] ^ rand[3];
  auth_data[0] = auth_data[0].toString(16);
  auth_data[1] = auth_data[1].toString(16);
  return auth_data;
}

function trim(str) {
  return str.replace(/(\s*$)/g, "");
}

module.exports = {
  formatTime,
  formatNumber,
  formatDate,
  trim,
  hexCharCodeToStr,
  ab2hex,
  getAuthenticationData,
  colors: ['#68348c', '#c499cf', '#8e3275', '#e50012', '#884707', '#575b49']
}
