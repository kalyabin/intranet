import * as Vue from "vue";
import Component from "vue-class-component";
import {roomListStore} from "../../../store/room-list.store";
import {RoomInterface} from "../../../service/model/room.interface";
import {Model} from "vue-property-decorator";
import {ManagerRoomForm} from "./room-form";

/**
 * Список переговорных комнат
 */
@Component({
    template: require('./room-list.html'),
    store: roomListStore,
    components: {
        'room-form': ManagerRoomForm
    }
})
export class ManagerRoomList extends Vue {
    /**
     * Показать форму
     */
    @Model() viewForm: boolean = false;

    /**
     * Текущий редактируемый элемент
     */
    @Model() currentItem: RoomInterface = null;

    /**
     * Колбек на создание элемента
     */
    roomCreated(room: RoomInterface): void {
        this.$store.commit('addItem', room);
        this.viewForm = false;
    }

    /**
     * Колбек на редактирование элемента
     */
    roomUpdated(room: RoomInterface): void {
        this.$store.commit('updateItem', room);
        this.viewForm = false;
    }

    /**
     * Открыть форму редактирования
     */
    openForm(room?: RoomInterface): void {
        this.currentItem = room;
        this.viewForm = true;
    }

    /**
     * Получить список элементов для отображения
     */
    get list(): RoomInterface[] {
        return this.$store.state.list;
    }

    mounted(): void {
        this.$store.dispatch('fetchManagerList');
    }

    destroyed(): void {
        this.$store.commit('clearList');
    }
}
