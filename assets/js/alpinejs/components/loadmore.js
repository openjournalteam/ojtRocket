document.addEventListener("alpine:init", () => {
  Alpine.data("loadmore", (sectionIds, url) => ({
    loading: false,
    init() {
      console.log(sectionIds);
    },
    key: 22,
    getLength() {
      return sectionIds.length;
    },
    more() {
      var that = this;
      if (this.key > this.getLength()) {
        return false;
      }
      this.loading = true;
      const sectionId = sectionIds[this.key];

      $.get(url + "?sectionId=" + sectionId, function (data) {
        if (!data) {
          that.more();
          return;
        }
        $(".sections").append(data);
        that.loading = false;
      });

      this.key++;
    },
  }));
});
