<div id="rocket_load_more" x-data="loadmore({$sectionIds}, '{$paginationUrl}')" x-intersect="more">
  <div class="loadingio-spinner-spinner-7uw6ur9hal7" x-show="loading" x-transition>
    <div class="ldio-bds5unioz0m">
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
    </div>
  </div>
</div>


<style type="text/css">
  #rocket_load_more {
    display: flex;
  }

  .loadingio-spinner-spinner-7uw6ur9hal7{
    margin-left:auto;
    margin-right:auto;
  }

  @keyframes ldio-bds5unioz0m {
    0% {
      opacity: 1
    }

    100% {
      opacity: 0
    }
  }

  .ldio-bds5unioz0m div {
    left: 47px;
    top: 24px;
    position: absolute;
    animation: ldio-bds5unioz0m linear 1s infinite;
    background: #93dbe9;
    width: 6px;
    height: 12px;
    border-radius: 3px / 6px;
    transform-origin: 3px 26px;
  }

  .ldio-bds5unioz0m div:nth-child(1) {
    transform: rotate(0deg);
    animation-delay: -0.9166666666666666s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(2) {
    transform: rotate(30deg);
    animation-delay: -0.8333333333333334s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(3) {
    transform: rotate(60deg);
    animation-delay: -0.75s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(4) {
    transform: rotate(90deg);
    animation-delay: -0.6666666666666666s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(5) {
    transform: rotate(120deg);
    animation-delay: -0.5833333333333334s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(6) {
    transform: rotate(150deg);
    animation-delay: -0.5s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(7) {
    transform: rotate(180deg);
    animation-delay: -0.4166666666666667s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(8) {
    transform: rotate(210deg);
    animation-delay: -0.3333333333333333s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(9) {
    transform: rotate(240deg);
    animation-delay: -0.25s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(10) {
    transform: rotate(270deg);
    animation-delay: -0.16666666666666666s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(11) {
    transform: rotate(300deg);
    animation-delay: -0.08333333333333333s;
    background: #93dbe9;
  }

  .ldio-bds5unioz0m div:nth-child(12) {
    transform: rotate(330deg);
    animation-delay: 0s;
    background: #93dbe9;
  }

  .loadingio-spinner-spinner-7uw6ur9hal7 {
    width: 64px;
    height: 64px;
    display: inline-block;
    overflow: hidden;
    background: none;
  }

  .ldio-bds5unioz0m {
    width: 100%;
    height: 100%;
    position: relative;
    transform: translateZ(0) scale(0.64);
    backface-visibility: hidden;
    transform-origin: 0 0;
    /* see note above */
  }

  .ldio-bds5unioz0m div {
    box-sizing: content-box;
  }

  /* generated by https://loading.io/ */
</style>
<script>
  $('.sections').after($("#rocket_load_more"));
</script>
<script src="{$ojtRocket->getAssetUrl('js/alpinejs/components/loadmore.js')}"></script>
<script defer src="{$ojtRocket->getAssetUrl('js/alpinejs/intersect.js')}"></script>
<script defer src="{$ojtRocket->getAssetUrl('js/alpinejs/alpine.js')}"></script>