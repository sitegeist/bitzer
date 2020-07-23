import React from "react";
import mergeClassNames from "classnames";

import {config as brandConfig} from "@neos-project/brand";
import {config as buildConfig} from "@neos-project/build-essentials/src/styles/styleConstants";
import {Dialog, Button} from '@neos-project/react-ui-components';

import { NAMESPACE, TODAY } from "./constants";

function getAnyTasksAreDueOrUpcoming(data) {
    if (data === null) {
        return false;
    }

    let sum = 0;

    if (data.numberOfTasksDue) {
        sum += Number(data.numberOfTasksDue);
    }

    if (data.numberOfTasksPastDue) {
        sum += Number(data.numberOfTasksPastDue);
    }

    if (data.numberOfUpcomingTasks) {
        sum += Number(data.numberOfUpcomingTasks);
    }

    return sum > 0;
}

function Metric(props) {
    return (
        <div
            style={{
                fontSize: buildConfig.fontSize.base,
                color: 'white',
                padding: `0 ${buildConfig.spacing.half}`,
                lineHeight: buildConfig.spacing.goldenUnit,
                backgroundColor: mergeClassNames({
                    [brandConfig.colors.primaryBlue]: props.info,
                    [brandConfig.colors.warn]: props.warn,
                    [brandConfig.colors.error]: props.danger
                })
            }}
        >
            {props.children}
        </div>
    )
}

export class Reminder extends React.Component {
    state = {
        data: null,
        storage: JSON.parse(localStorage.getItem(NAMESPACE) || '{}')
    }

    async componentDidMount() {
        const {storage} = this.state;

        if (!storage.latestUpdate || storage.latestUpdate !== TODAY) {
            const result = await fetch('/sitegeist/bitzer/api/due-tasks');
            const json = await result.json();

            this.setState({data: json});
        }
    }

    handleGoToBitzer = () => {
        const {data} = this.state;

        if (data) {
            location.href = data.links.module;
        }
    }

    render() {
        const {i18n} = this.props;
        const {data, storage} = this.state;

        if (getAnyTasksAreDueOrUpcoming(data)) {
            localStorage.setItem(NAMESPACE, JSON.stringify({
                ...storage,
                latestUpdate: TODAY
            }));

            return (
                <Dialog
                    title={i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:reminder.title')}
                    style="narrow"
                    isOpen
                >
                    <div style={{padding: buildConfig.spacing.full}}>
                        <p style={{marginTop: 0, marginBottom: buildConfig.spacing.full}}>
                            {i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:reminder.introduction')}
                        </p>
                        <ul style={{marginBottom: buildConfig.spacing.full}}>
                            {data.numberOfTasksPastDue ? (
                                <li style={{marginBottom: buildConfig.spacing.half}}>
                                    <Metric danger>
                                        {i18n.translate(
                                            data.numberOfTasksPastDue === 1
                                                ? 'Sitegeist.Bitzer:NeosUiPlugin:reminder.taskPastDue'
                                                : 'Sitegeist.Bitzer:NeosUiPlugin:reminder.tasksPastDue',
                                            '',
                                            {0: data.numberOfTasksPastDue}
                                        )}
                                    </Metric>
                                </li>
                            ) : null}
                            {data.numberOfTasksDue ? (
                                <li style={{marginBottom: buildConfig.spacing.half}}>
                                    <Metric warn>
                                        {i18n.translate(
                                            data.numberOfTasksDue === 1
                                                ? 'Sitegeist.Bitzer:NeosUiPlugin:reminder.taskDue'
                                                : 'Sitegeist.Bitzer:NeosUiPlugin:reminder.tasksDue',
                                            '',
                                            {0: data.numberOfTasksDue}
                                        )}
                                    </Metric>
                                </li>
                            ) : null}
                            {data.numberOfUpcomingTasks ? (
                                <li style={{marginBottom: buildConfig.spacing.half}}>
                                    <Metric info>
                                        {i18n.translate(
                                            data.numberOfUpcomingTasks === 1
                                                ? 'Sitegeist.Bitzer:NeosUiPlugin:reminder.taskUpcoming'
                                                : 'Sitegeist.Bitzer:NeosUiPlugin:reminder.tasksUpcoming',
                                            '',
                                            {0: data.numberOfUpcomingTasks}
                                        )}
                                    </Metric>
                                </li>
                            ) : null}
                        </ul>
                        <div style={{display: 'flex', justifyContent: 'space-between'}}>
                            <Button onClick={() => this.setState({data: null})}>
                                {i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:reminder.action.dismiss')}
                            </Button>

                            <Button style="warn" onClick={this.handleGoToBitzer}>
                                {i18n.translate('Sitegeist.Bitzer:NeosUiPlugin:reminder.action.goToSchedule')}
                            </Button>
                        </div>
                    </div>
                </Dialog>
            );
        }
        else {
            return null;
        }
    }
}
