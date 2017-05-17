/**
 * Конфигурация валидаторов форм
 *
 * @see http://vee-validate.logaretm.com/
 */

export const validateConfiguration = {
    locale: 'ru',
    dictionary: {
        ru: {
            messages: {
                email: () => 'Неверный формат e-mail',
                required: () => 'Поле обязательно для заполнения'
            }
        }
    }
};
