<template lang="pug">

  div(
    class="user-view"
    v-if="entity.loaded"
    :class="{selected: isUserSelected}"
    @click.stop="$emit('onUserClicked', entity, isUserSelected)"
  )
    img(
      :src="entity.getAvatar()"
    )
    div.name {{ entity.getName() }}
    i.fi.fi-rr-check(
      v-if="isUserSelected"
    )

</template>

<script lang="js">
import lodash from "lodash";
import { mapState } from "vuex";

export default {
  name: 'user-view',
  props: [
    'entity'
  ],
  mounted() {
  },
  data() {
    return {
      name: 'loading',
    }
  },
  methods: {},
  computed: {
    ...mapState([
      'selectedIds',
    ]),
    isUserSelected: function () {
      let res = lodash.includes(this.selectedIds, this.entity.id);
      return res;
    }
  }
}
</script>
