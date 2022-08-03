<template>
  <div class="wrapper">
    <ul class="list">
      <li>
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator"></div>
          </div>
        </div>
        <div class="head">
          <h4>Message 1</h4>
        </div>
        <div class="body">
          <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint laudantium velit quidem eveniet facere earum porro nulla!</span>
        </div>
        <div class="timestamp">
          <span>15:20</span>
        </div>
      </li>
      <li>
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator"></div>
          </div>
        </div>
        <div class="head">
          <h4>Message 1</h4>
        </div>
        <div class="body">
          <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint laudantium velit quidem eveniet facere earum porro nulla!</span>
        </div>
        <div class="timestamp">
          <span>15:20</span>
        </div>
      </li>
      <li>
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator"></div>
          </div>
        </div>
        <div class="head">
          <h4>Message 1</h4>
        </div>
        <div class="body">
          <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint laudantium velit quidem eveniet facere earum porro nulla!</span>
        </div>
        <div class="timestamp">
          <span>Yesterday</span>
        </div>
      </li>
      <li>
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator"></div>
          </div>
        </div>
        <div class="head">
          <h4>Message 1</h4>
        </div>
        <div class="body">
          <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint laudantium velit quidem eveniet facere earum porro nulla!</span>
        </div>
        <div class="timestamp">
          <span>Sep 4</span>
        </div>
      </li>
      <li>
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator"></div>
          </div>
        </div>
        <div class="head">
          <h4>Message 1</h4>
        </div>
        <div class="body">
          <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint laudantium velit quidem eveniet facere earum porro nulla!</span>
        </div>
        <div class="timestamp">
          <span>Aug 6</span>
        </div>
      </li>
      <li>
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator"></div>
          </div>
        </div>
        <div class="head">
          <h4>Message 1</h4>
        </div>
        <div class="body">
          <span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint laudantium velit quidem eveniet facere earum porro nulla!</span>
        </div>
        <div class="timestamp">
          <span>Jan 4</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
  export default {
    name: 'messages-view'
  }
</script>

<style
  lang="scss"
  scoped
  src="~v/notifications.scss"
></style>