<template>
  <div id="card">
    <vue-flip active-click="true" width="320px" height="200px">
      <template v-slot:front>
        <div class="credit-card front">
          <div class="card-chip">
            1234
          </div>
          <div class="card-number">1234 5678 9012 3456</div>
          <div class="card-info">
            <div class="card-holder">
              <span>Card Holder</span>
              <span>John Doe</span>
            </div>
            <div class="card-expiration">
              <span>Expires</span>
              <span>12/24</span>
            </div>
          </div>
        </div>
      </template>
      <template v-slot:back>
        <div class="credit-card back">
          <div class="card-chip">&nbsp;</div>
          <div class="card-number">&nbsp;</div>
          <div class="card-info">
            <div class="card-holder">
              <span>Card Holder</span>
              <span>John Doe</span>
            </div>
            <div class="card-expiration">
              <span></span>
              <span>010</span>
            </div>
          </div>
        </div>
      </template>
    </vue-flip>
  </div>
</template>

<script>
import { VueFlip } from 'vue-flip';

export default {
  name: 'Card',
  components: {
    'vue-flip': VueFlip
  },
  mounted() {
    console.log("Mounting Vue Flip")
  }

}
</script>

<style scoped>
#Card {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
.back {
  background: linear-gradient(135deg, darkred, darkblue);
  width: 320px;
  height: 200px;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
}
.front {
  background: linear-gradient(135deg, #667eea, #764ba2);
  width: 320px;
  height: 200px;
  border-radius: 15px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  color: #fff;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
}
.credit-card {

}

.card-chip {
  width: 50px;
  height: 40px;
}

</style>
