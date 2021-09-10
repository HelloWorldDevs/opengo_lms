<template lang="pug">

  section(
    class="group-view"
    v-if="entity.loaded"
    :class="{selected: isGroupSelected}"
    @click="onGroupClicked"
  )
    img(
      :src="entity.getAvatar()"
    )
    div.name {{ entity.getName() }}
    i.fi.fi-rr-users(@click.stop="setGroupID")
    i.fi.fi-rr-check(
      v-if="isGroupSelected"
    )

</template>

<script lang="js">
  import { mapState } from "vuex";
  import lodash from "lodash";

  export default  {
    name: 'group-view',
    props: [
      'entity'
    ],
    mounted() {

    },
    data() {
      return {

      }
    },
    methods: {
      onGroupClicked: function (){
        this.$emit('onGroupClicked', this.entity, this.isGroupSelected)
      },
      setGroupID: function (){
        this.$emit('viewGroup', this.entity)
      },
      isGroupKeyInSelected: function (){
        return lodash.includes(this.selectedGroupsIds, this.entity.id);
      },
      isAllUsersIdInSelected: function (){
        let res = lodash.intersection([...this.selectedIds], lodash.map(this.entity.members));
        return res.length==lodash.map(this.entity.members).length
      },
    },
    computed: {
      ...mapState([
        'selectedIds',
        'selectedGroupsIds',
      ]),
      isGroupSelected: function (){
        return this.isGroupKeyInSelected() && this.isAllUsersIdInSelected()
      }
    }
}
</script>
