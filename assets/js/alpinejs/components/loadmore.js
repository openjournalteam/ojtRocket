document.addEventListener("alpine:init", () => {
  Alpine.data("loadmore", (sectionIds, url) => ({
    loading: false,
    key: 1,
    init() {},
    sectionIdsLength: () => sectionIds.length,
    more() {
      var that = this;
      if (this.key >= this.sectionIdsLength()) {
        return false;
      }
      this.loading = true;

      const sectionId = sectionIds[this.key];

      this.key++;

      $.get(url + "?sectionId=" + sectionId, function (data) {
        if (!data) {
          that.more();
          return;
        }
        $(".sections").append(data);
        that.loading = false;
      });
    },
  }));
});
