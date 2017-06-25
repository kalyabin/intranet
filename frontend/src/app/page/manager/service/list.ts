import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {ServiceInterface} from "../../../service/model/service.interface";
import {extendedServicesService} from "../../../service/extended-services-service";
import {ModalWindow} from "../../../components/modal-window";
import {ManagerServiceForm} from "./form";

/**
 * Управление дополнительными услугами - менеджмент
 */
@Component({
    template: require('./list.html'),
    components: {
        'service-form': ManagerServiceForm
    }
})
export class ManagerServiceList extends Vue {
    /**
     * Список услуг
     */
    @Model() list: Array<ServiceInterface> = [];

    /**
     * Есть неактивные услуги
     */
    @Model() hasInactive: boolean = false;

    /**
     * Текущая редактируемая услуга
     */
    @Model() currentService: ServiceInterface = null;

    /**
     * Показать форму редактирования или создания услуги
     */
    @Model() viewForm: boolean = false;

    protected fetchList(): void {
        this.hasInactive = false;
        this.list = [];
        extendedServicesService.managerList().then((response) => {
            this.list = response.list;
            for (let item of this.list) {
                if (!item.isActive) {
                    this.hasInactive = true;
                    break;
                }
            }
        });
    }

    beforeDestroy(): void {
        this.list = [];
    }

    mounted(): void {
        this.fetchList();
    }

    /**
     * Открыть форму редактирования или создания услуги
     */
    openDialog(service?: ServiceInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.show();

        this.currentService = service;
        this.viewForm = true;
    }

    /**
     * Создана новая услуга
     */
    newService(service: ServiceInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        if (!service.isActive && !this.hasInactive) {
            this.hasInactive = true;
        }

        this.list.push(service);
    }

    /**
     * Обновить модель услуги
     */
    updatedService(service: ServiceInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.hasInactive = false;
        for (let i in this.list) {
            if (this.list[i].id == service.id) {
                this.list[i] = service;
            }
            if (!this.list[i].isActive) {
                this.hasInactive = true;
            }
        }
    }

    /**
     * Удалить услугу
     */
    removedService(id: string): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        for (let i in this.list) {
            if (this.list[i].id == id) {
                this.list.splice(parseInt(i), 1);
            }
        }
    }
}
