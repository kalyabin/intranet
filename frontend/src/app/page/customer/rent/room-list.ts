import Vue from "vue";
import Component from "vue-class-component";
import {roomListStore} from "../../../store/room-list.store";
import {RoomInterface} from "../../../service/model/room.interface";

/**
 * Список переговорных комнат для арендатора
 */
@Component({
    template: require('./room-list.html'),
    store: roomListStore
})
export class CustomerRoomList extends Vue {
    /**
     * Получить список комнат
     */
    get list(): RoomInterface[] {
        return this.$store.state.list;
    }

    mounted(): void {
        this.$store.dispatch('fetchCustomerList');
    }

    destroyed(): void {
        this.$store.commit('clearList');
    }
}
