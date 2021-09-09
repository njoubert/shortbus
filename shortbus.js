class MessageBus {
  constructor(next) {
    this.url = "http://shortbus.njoubert.com/"
    this.rate = 500;
    this.user = -1;
    this.lastId = -1;
    this.lastTime = null;
    this.callbacks = [];
    this.connect(next);
  }
  
  construct_url(params) {
    var url = new URL(this.url);
    url.search = new URLSearchParams(params).toString();
    return url;
  }

  connect(next) {
    var that = this;
    fetch(this.construct_url({}))
      .then(response => {
        if (!response.ok) {
          throw new Error('MessageBus: Network Response was not ok');
        }
        return response.json();
      })
      .then(msg => {
        that.user = msg.user;
        that.lastId = msg.id;
        that.poller = setInterval(that.poll.bind(that), this.rate);
        if (next) next();
      });
  }

  poll() {
    if (this.user === -1 || this.lastId === -1)
      return; // No user ID yet.
    var that = this;
    fetch(this.construct_url({user:this.user,id:this.lastId}))
      .then(response => {
        if (!response.ok) {
          throw new Error('MessageBus: Network Response was not ok');
        }
        return response.json();
      })
      .then(msg => {
        that.lastTime = Date.now();
        if (msg.length > 0) {
            that.lastId = m.id;
            that.callbacks.forEach(cb => { cb(m.data); });
          });
        }
       });   
  }

  send(data) {
    if (this.user === -1)
      return; // No user ID yet.
    fetch(this.construct_url({user:this.user}),
    {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data),
    }).then(response => {
      if (!response.ok) {
        throw new Error('MessageBus: Network resonse was not ok.');
      }
    });
  }

  addEventListener(callback) {
    this.callbacks.push(callback);
  }
}

