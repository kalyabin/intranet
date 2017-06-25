import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {ServiceInterface} from "../../../service/model/service.interface";
import {rolesListStore} from "../../../store/roles-list.store";
import {extendedServicesService} from "../../../service/extended-services-service";
import {ServiceResponseInterface} from "../../../service/response/service-response.interface";
import {ServiceTariffInterface} from "../../../service/model/service-tariff.interface";

/**
 * Форма управления услугой
 */
@Component({
    template: require('./form.html')
})
export class ManagerServiceForm extends Vue {
    /**
     * Входной параметр - модель редактируемой услуги
     */
    @Prop(Object) inputService: ServiceInterface;

    /**
     * Редактирование
     */
    @Model() existent: boolean = !!(this.inputService && this.inputService.id);

    /**
     * Данные для редактирования
     */
    @Model() service: ServiceInterface = this.inputService ? this.inputService :  {
        id: null,
        title: null,
        isActive: true,
        description: null,
        customerRole: null,
        tariff: []
    };

    /**
     * Ожидание субмита формы
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Текст ошибки
     */
    @Model() errorMessage: string = '';

    /**
     * Получить роли арендатора
     */
    get customerRoles(): string[] {
        return rolesListStore.state.rolesByUserType['customer'] || [];
    }

    /**
     * Подписи для ролей
     */
    get rolesLabels(): {[key: string]: string} {
        return rolesListStore.state.rolesLabels;
    }

    mounted(): void {
        rolesListStore.dispatch('fetchData');
    }

    /**
     * Субмит формы
     */
    submit(): void {
        this.errorMessage = '';

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            if (this.existent) {
                extendedServicesService.update(this.inputService.id, this.service).then((response: ServiceResponseInterface) => {
                    this.awaitOfSubmit = false;
                    if (response.success && response.service) {
                        this.$emit('service:updated', response.service);
                    } else {
                        this.errorMessage = response.firstError;
                    }
                });
            } else {
                extendedServicesService.create(this.service).then((response: ServiceResponseInterface) => {
                    this.awaitOfSubmit = false;
                    if (response.success && response.service) {
                        this.$emit('service:new', response.service);
                    } else {
                        this.errorMessage = response.firstError;
                    }
                });
            }
        }, () => {});
    }

    /**
     * Удаление услуги
     */
    remove(): void {
        if (confirm('Вы уверены что хотите удалить данную услугу? Услуга будет удалена полностью, включая тех арендаторов, которые уже успели ее активировать.')) {
            this.errorMessage = '';

            this.awaitOfSubmit = true;
            extendedServicesService.remove(this.inputService.id).then((response) => {
                this.awaitOfSubmit = false;

                if (response.success) {
                    this.$emit('service:removed', this.inputService.id);
                }
            });
        }
    }

    /**
     * Добавить тариф
     */
    addTariff(): void {
        this.service.tariff.push({
            title: null,
            monthlyCost: null,
            isActive: true
        });
    }

    /**
     * Удалить тариф по индексу
     */
    removeTariff(index: number): void {
        if (confirm('Вы уверены, что хотите удалить данный тариф?')) {
            this.service.tariff.splice(index, 1);
        }
    }
}
