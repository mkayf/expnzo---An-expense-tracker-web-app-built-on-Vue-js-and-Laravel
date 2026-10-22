import {
    ChartBarSquareIcon,
    DocumentCurrencyDollarIcon,
    Square3Stack3DIcon,
    ArrowsRightLeftIcon 
} from "@heroicons/vue/24/outline";

const menus = [
    {
        name: 'Dashboard',
        path: '/app/dashboard',
        icon: ChartBarSquareIcon
    },
    {
        name: 'Transactions',
        path: '/app/transactions',
        icon: ArrowsRightLeftIcon
    },
    {
        name: 'Categories',
        path: '/app/categories',
        icon: Square3Stack3DIcon
    },
    {
        name: 'Reports',
        path: '/app/reports',
        icon: DocumentCurrencyDollarIcon
    },
];

export default menus;