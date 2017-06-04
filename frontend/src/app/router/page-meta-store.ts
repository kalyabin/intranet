import Vuex from "vuex";

/**
 * Конфигурация meta-данных
 */
export interface PageMetaStateInterface {
    pageTitle: string;
    title: string;
    pageLoader: boolean;
    pageLoaderCounter: number;
}

/**
 * Состояние мета-данных страницы
 */
export const pageMetaStore = new Vuex.Store({
   state:  <PageMetaStateInterface>{
       pageTitle: '',
       title: '',
       pageLoader: false,
       pageLoaderCounter: 0
   },
    mutations: {
        setPageTitle: (state: PageMetaStateInterface, pageTitle: string) => {
           state.pageTitle = pageTitle;
        },
        setTitle: (state: PageMetaStateInterface, title: string) => {
            state.title = title;
        },
        showPageLoader: (state: PageMetaStateInterface) => {
            state.pageLoaderCounter++;
            if (!state.pageLoader) {
                state.pageLoader = true;
            }
        },
        hidePageLoader: (state: PageMetaStateInterface) => {
            state.pageLoaderCounter--;
            state.pageLoaderCounter = Math.max(state.pageLoaderCounter, 0);
            if (state.pageLoaderCounter < 1) {
                state.pageLoader = false;
            }
        },
    }
});
